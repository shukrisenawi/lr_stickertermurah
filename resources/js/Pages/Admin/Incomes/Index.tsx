import ExpensesIndex from '@/Pages/Admin/Expenses/Index';
import type { ComponentProps } from 'react';

type SharedProps = ComponentProps<typeof ExpensesIndex>;
type IncomeRecord = Omit<SharedProps['expenses']['data'][number], 'expense_category_id' | 'purchase_date'> & {
  income_category_id: number | null;
  received_date: string;
};
type Props = Omit<SharedProps, 'expenses' | 'income'> & {
  incomes: { data: IncomeRecord[]; links: SharedProps['expenses']['links'] };
};

export default function IncomesIndex({ incomes, ...props }: Props) {
  const expenses = {
    ...incomes,
    data: incomes.data.map(({ income_category_id, received_date, ...record }) => ({
      ...record, expense_category_id: income_category_id, purchase_date: received_date,
    })),
  };

  return <ExpensesIndex {...props} expenses={expenses} income />;
}
