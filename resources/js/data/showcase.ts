export interface ShowcaseDesign {
  id: number;
  name: string;
  category: string;
  image: string;
}

export const showcaseDesigns: ShowcaseDesign[] = [
  { id: 1, name: 'Donut Ketagih', category: 'Bakery', image: '/images/showcase/sticker-01.webp' },
  { id: 2, name: 'Delima Bakery', category: 'Bakery', image: '/images/showcase/sticker-02.webp' },
  { id: 3, name: 'Syurga Bakery', category: 'Bakery', image: '/images/showcase/sticker-03.webp' },
  { id: 4, name: 'Roti Bakar Legend', category: 'Bakery', image: '/images/showcase/sticker-04.webp' },
  { id: 5, name: 'Cookies Gebu', category: 'Bakery', image: '/images/showcase/sticker-05.webp' },
  { id: 6, name: 'Impiana Bakery', category: 'Bakery', image: '/images/showcase/sticker-06.webp' },
  { id: 7, name: 'Butter Bliss Bakery', category: 'Bakery', image: '/images/showcase/sticker-07.webp' },
  { id: 8, name: 'Luna Bakery', category: 'Bakery', image: '/images/showcase/sticker-08.webp' },
  { id: 9, name: 'Alya Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-09.webp' },
  { id: 10, name: 'Selera Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-10.webp' },
  { id: 11, name: 'SH Best Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-11.webp' },
  { id: 12, name: 'Syurga Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-12.webp' },
  { id: 13, name: 'Qaseh Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-13.webp' },
  { id: 14, name: 'Bintang Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-14.webp' },
  { id: 15, name: 'Kakak Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-15.webp' },
  { id: 16, name: 'Chicken Chop Viral', category: 'Makanan', image: '/images/showcase/sticker-16.webp' },
  { id: 17, name: 'Martabak Special', category: 'Makanan', image: '/images/showcase/sticker-17.webp' },
  { id: 18, name: 'Laksa Utara Ori', category: 'Makanan', image: '/images/showcase/sticker-18.webp' },
  { id: 19, name: 'Nasi Ayam Padu', category: 'Makanan', image: '/images/showcase/sticker-19.webp' },
  { id: 20, name: 'Ayam Crispy Viral', category: 'Makanan', image: '/images/showcase/sticker-20.webp' },
  { id: 21, name: 'Ayam Gunting Legend', category: 'Makanan', image: '/images/showcase/sticker-21.webp' },
  { id: 22, name: 'Satay Kayangan', category: 'Makanan', image: '/images/showcase/sticker-22.webp' },
  { id: 23, name: 'Burger Leleh Padu', category: 'Makanan', image: '/images/showcase/sticker-23.webp' },
  { id: 24, name: 'Sambal Padu Mak Teh', category: 'Makanan', image: '/images/showcase/sticker-24.webp' },
  { id: 25, name: 'Mochi Ice Cream', category: 'Minuman & Dessert', image: '/images/showcase/sticker-25.webp' },
  { id: 26, name: 'Waffle Meleleh', category: 'Minuman & Dessert', image: '/images/showcase/sticker-26.webp' },
  { id: 27, name: 'Jus Buah Segar', category: 'Minuman & Dessert', image: '/images/showcase/sticker-27.webp' },
  { id: 28, name: 'Teh Ais Ketagih', category: 'Minuman & Dessert', image: '/images/showcase/sticker-28.webp' },
  { id: 29, name: 'Pulut Mangga Heaven', category: 'Minuman & Dessert', image: '/images/showcase/sticker-29.webp' },
  { id: 30, name: 'Cucur Badak Special', category: 'Snack & Kuih', image: '/images/showcase/sticker-30.webp' },
  { id: 31, name: 'Kacang Pedas Crunch', category: 'Snack & Kuih', image: '/images/showcase/sticker-31.webp' },
  { id: 32, name: 'Biskut Pandan Delight', category: 'Snack & Kuih', image: '/images/showcase/sticker-32.webp' },
  { id: 33, name: 'Honey Cornflakes Viral', category: 'Snack & Kuih', image: '/images/showcase/sticker-33.webp' },
  { id: 34, name: 'Cornflakes Madu Special', category: 'Snack & Kuih', image: '/images/showcase/sticker-34.webp' },
  { id: 35, name: 'Tepung Pelita Opah', category: 'Snack & Kuih', image: '/images/showcase/sticker-35.webp' },
];

export const showcaseCategories = [
  'Semua',
  'Bakery',
  'Kitchen',
  'Makanan',
  'Minuman & Dessert',
  'Snack & Kuih',
] as const;
