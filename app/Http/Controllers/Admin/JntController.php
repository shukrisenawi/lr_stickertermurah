<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JntController extends Controller
{
    public function index(Request $request): View
    {
        $selectedOrder = null;
        $selectedOrderId = (int) $request->integer('order_id');

        if ($selectedOrderId > 0) {
            $selectedOrder = Order::query()->find($selectedOrderId);
        }

        $activeTab = $request->string('tab')->toString();
        if (! in_array($activeTab, ['create', 'tracking', 'list'], true)) {
            $activeTab = 'create';
        }

        $waybillSearch = trim($request->string('waybill_q')->toString());

        $waybills = Order::query()
            ->whereNotNull('tracking_no')
            ->when($waybillSearch !== '', function ($query) use ($waybillSearch) {
                $query->where(function ($subQuery) use ($waybillSearch) {
                    $subQuery
                        ->where('tracking_no', 'like', "%{$waybillSearch}%")
                        ->orWhere('order_no', 'like', "%{$waybillSearch}%")
                        ->orWhere('customer_name', 'like', "%{$waybillSearch}%")
                        ->orWhere('customer_phone', 'like', "%{$waybillSearch}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->appends([
                'tab' => 'list',
                'waybill_q' => $waybillSearch,
            ]);

        return view('admin.jnt.index', [
            'orders' => Order::query()->latest()->limit(100)->get(),
            'selectedOrder' => $selectedOrder,
            'waybillResult' => session('jnt_waybill_result'),
            'trackingResult' => session('jnt_tracking_result'),
            'activeTab' => $activeTab,
            'waybills' => $waybills,
            'waybillSearch' => $waybillSearch,
        ]);
    }

    public function createWaybill(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'txlogistic_id' => ['nullable', 'string', 'max:50'],
            'express_type' => ['required', 'string', 'in:EZ,EX,FD,DO,JS'],
            'service_type' => ['required', 'string', 'in:1,6'],
            'pay_type' => ['required', 'string', 'in:PP_PM,PP_CASH,CC_CASH'],
            'receiver_name' => ['required', 'string', 'max:100'],
            'receiver_phone' => ['required', 'string', 'max:30'],
            'receiver_country_code' => ['required', 'string', 'max:3'],
            'receiver_postcode' => ['required', 'string', 'max:10'],
            'receiver_address' => ['required', 'string', 'max:200'],
            'item_name' => ['required', 'string', 'max:100'],
            'item_quantity' => ['required', 'integer', 'min:1', 'max:9999999'],
            'item_weight' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'item_value' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'package_quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'package_weight' => ['required', 'numeric', 'min:0.01', 'max:999.99'],
            'package_value' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'goods_type' => ['required', 'string', 'in:ITN2,ITN8'],
            'package_length' => ['nullable', 'numeric', 'min:0.01', 'max:999.99'],
            'package_width' => ['nullable', 'numeric', 'min:0.01', 'max:999.99'],
            'package_height' => ['nullable', 'numeric', 'min:0.01', 'max:999.99'],
            'remark' => ['nullable', 'string', 'max:200'],
        ]);

        $this->ensureJntConfig();

        $order = null;
        if (! empty($validated['order_id'])) {
            $order = Order::query()->findOrFail($validated['order_id']);
        }

        $txLogisticId = $validated['txlogistic_id']
            ?? ($order?->order_no ? Str::of($order->order_no)->replace(' ', '')->upper()->toString() : null)
            ?? ('ORD' . now()->format('YmdHis') . random_int(100, 999));

        $bizContent = [
            'customerCode' => (string) config('services.jnt.customer_code'),
            'password' => (string) config('services.jnt.password'),
            'txlogisticId' => $txLogisticId,
            'actionType' => 'add',
            'serviceType' => $validated['service_type'],
            'payType' => $validated['pay_type'],
            'expressType' => $validated['express_type'],
            'sender' => [
                'name' => (string) config('services.jnt.sender_name'),
                'phone' => (string) config('services.jnt.sender_phone'),
                'countryCode' => (string) config('services.jnt.sender_country_code'),
                'address' => (string) config('services.jnt.sender_address'),
                'postCode' => (string) config('services.jnt.sender_postcode'),
            ],
            'receiver' => [
                'name' => $validated['receiver_name'],
                'phone' => $validated['receiver_phone'],
                'countryCode' => Str::upper($validated['receiver_country_code']),
                'address' => $validated['receiver_address'],
                'postCode' => $validated['receiver_postcode'],
            ],
            'items' => [[
                'itemName' => $validated['item_name'],
                'number' => (int) $validated['item_quantity'],
                'weight' => (string) $validated['item_weight'],
                'itemValue' => (string) $validated['item_value'],
                'itemCurrency' => 'MYR',
            ]],
            'packageInfo' => Arr::whereNotNull([
                'packageQuantity' => (int) $validated['package_quantity'],
                'goodsType' => $validated['goods_type'],
                'weight' => (string) $validated['package_weight'],
                'packageValue' => (string) $validated['package_value'],
                'length' => isset($validated['package_length']) ? (string) $validated['package_length'] : null,
                'width' => isset($validated['package_width']) ? (string) $validated['package_width'] : null,
                'height' => isset($validated['package_height']) ? (string) $validated['package_height'] : null,
            ]),
            'remark' => $validated['remark'] ?? null,
        ];

        $response = $this->requestJnt('/webopenplatformapi/api/order/addOrder', $bizContent);

        $code = (string) ($response['code'] ?? '');
        if (in_array($code, ['1', '11'], true)) {
            $billCode = data_get($response, 'data.billCode');
            if ($order && $billCode) {
                $order->update([
                    'tracking_no' => $billCode,
                ]);
            }

            return redirect()
                ->route('admin.jnt.index', ['order_id' => $validated['order_id'] ?? null, 'tab' => 'create'])
                ->with('success', 'Waybill berjaya dicipta.')
                ->with('jnt_waybill_result', $response);
        }

        return redirect()
            ->route('admin.jnt.index', ['order_id' => $validated['order_id'] ?? null, 'tab' => 'create'])
            ->with('error', 'Waybill gagal dicipta: ' . ($response['msg'] ?? 'Unknown error'))
            ->with('jnt_waybill_result', $response);
    }

    public function checkTracking(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_code' => ['nullable', 'string', 'max:30'],
            'txlogistic_id' => ['nullable', 'string', 'max:50'],
        ]);

        if (blank($validated['bill_code'] ?? null) && blank($validated['txlogistic_id'] ?? null)) {
            return redirect()
                ->route('admin.jnt.index', ['tab' => 'tracking'])
                ->with('error', 'Isi sekurang-kurangnya Bill Code atau TxLogistic ID.');
        }

        $this->ensureJntConfig();

        $bizContent = Arr::whereNotNull([
            'customerCode' => (string) config('services.jnt.customer_code'),
            'password' => (string) config('services.jnt.password'),
            'billCode' => $validated['bill_code'] ?: null,
            'txlogisticId' => $validated['txlogistic_id'] ?: null,
        ]);

        $response = $this->requestJnt('/webopenplatformapi/api/logistics/trace', $bizContent);

        $code = (string) ($response['code'] ?? '');
        if (in_array($code, ['1', '11'], true)) {
            return redirect()
                ->route('admin.jnt.index', ['tab' => 'tracking'])
                ->with('success', 'Tracking berjaya disemak.')
                ->with('jnt_tracking_result', $response);
        }

        return redirect()
            ->route('admin.jnt.index', ['tab' => 'tracking'])
            ->with('error', 'Tracking gagal: ' . ($response['msg'] ?? 'Unknown error'))
            ->with('jnt_tracking_result', $response);
    }

    private function ensureJntConfig(): void
    {
        $required = [
            'services.jnt.base_url',
            'services.jnt.api_account',
            'services.jnt.private_key',
            'services.jnt.customer_code',
            'services.jnt.password',
            'services.jnt.sender_name',
            'services.jnt.sender_phone',
            'services.jnt.sender_country_code',
            'services.jnt.sender_postcode',
            'services.jnt.sender_address',
        ];

        foreach ($required as $key) {
            abort_if(blank(config($key)), 422, "Sila tetapkan konfigurasi {$key} dalam .env");
        }
    }

    private function requestJnt(string $path, array $bizContent): array
    {
        $baseUrl = rtrim((string) config('services.jnt.base_url'), '/');
        $apiAccount = (string) config('services.jnt.api_account');
        $privateKey = (string) config('services.jnt.private_key');
        $timestamp = (string) now()->getTimestampMs();
        $bizContentJson = json_encode($bizContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($bizContentJson === false) {
            abort(422, 'bizContent tidak sah untuk JSON encoding.');
        }

        $digest = base64_encode(md5($bizContentJson . $privateKey, true));

        try {
            $httpResponse = Http::asForm()
                ->withHeaders([
                    'apiAccount' => $apiAccount,
                    'digest' => $digest,
                    'timestamp' => $timestamp,
                ])
                ->timeout(30)
                ->post($baseUrl . $path, [
                    'bizContent' => $bizContentJson,
                ]);
        } catch (\Throwable $e) {
            return [
                'code' => 0,
                'msg' => 'Request J&T gagal: ' . $e->getMessage(),
                'data' => null,
            ];
        }

        if (! $httpResponse->ok()) {
            return [
                'code' => 0,
                'msg' => "HTTP {$httpResponse->status()}",
                'data' => $httpResponse->body(),
            ];
        }

        $decoded = $httpResponse->json();

        if (! is_array($decoded)) {
            return [
                'code' => 0,
                'msg' => 'Respons J&T tidak boleh dibaca sebagai JSON.',
                'data' => $httpResponse->body(),
            ];
        }

        return $decoded;
    }
}
