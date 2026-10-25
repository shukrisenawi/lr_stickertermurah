import ExpenseCategoriesIndex from '@/Pages/Admin/ExpenseCategories/Index';

interface Props {
  categories: Array<{ id: number; name: string; incomes_count: number }>;
}

export default function IncomeCategoriesIndex({ categories }: Props) {
  return <ExpenseCategoriesIndex income categories={categories.map(({ incomes_count, ...category }) => ({
    ...category, expenses_count: incomes_count,
  }))} />;
}
