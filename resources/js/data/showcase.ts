export interface ShowcaseDesign {
  id: number;
  name: string;
  category: string;
  image: string;
  tags: string[];
}

export const showcaseDesigns: ShowcaseDesign[] = [
  { id: 1, name: 'Donut Ketagih', category: 'Bakery', image: '/images/showcase/sticker-01.webp', tags: ['bakery', 'donut', 'cookies', 'kuih'] },
  { id: 2, name: 'Delima Bakery', category: 'Bakery', image: '/images/showcase/sticker-02.webp', tags: ['bakery', 'cake', 'cookies', 'kedai'] },
  { id: 3, name: 'Syurga Bakery', category: 'Bakery', image: '/images/showcase/sticker-03.webp', tags: ['bakery', 'bread', 'roti', 'kedai'] },
  { id: 4, name: 'Roti Bakar Legend', category: 'Bakery', image: '/images/showcase/sticker-04.webp', tags: ['bakery', 'rotibakar', 'toast', 'breakfast'] },
  { id: 5, name: 'Cookies Gebu', category: 'Bakery', image: '/images/showcase/sticker-05.webp', tags: ['bakery', 'cookies', 'biskut', 'kuih'] },
  { id: 6, name: 'Impiana Bakery', category: 'Bakery', image: '/images/showcase/sticker-06.webp', tags: ['bakery', 'cake', 'birthday', 'kedai'] },
  { id: 7, name: 'Butter Bliss Bakery', category: 'Bakery', image: '/images/showcase/sticker-07.webp', tags: ['bakery', 'butter', 'cookies', 'premium'] },
  { id: 8, name: 'Luna Bakery', category: 'Bakery', image: '/images/showcase/sticker-08.webp', tags: ['bakery', 'moon', 'cookies', 'kedai'] },
  { id: 9, name: 'Alya Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-09.webp', tags: ['kitchen', 'dapur', 'cookies', 'frozen'] },
  { id: 10, name: 'Selera Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-10.webp', tags: ['kitchen', 'dapur', 'makanan', 'frozen'] },
  { id: 11, name: 'SH Best Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-11.webp', tags: ['kitchen', 'dapur', 'best', 'frozen'] },
  { id: 12, name: 'Syurga Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-12.webp', tags: ['kitchen', 'dapur', 'masakan', 'frozen'] },
  { id: 13, name: 'Qaseh Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-13.webp', tags: ['kitchen', 'dapur', 'sayang', 'frozen'] },
  { id: 14, name: 'Bintang Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-14.webp', tags: ['kitchen', 'dapur', 'bintang', 'frozen'] },
  { id: 15, name: 'Kakak Kitchen', category: 'Kitchen', image: '/images/showcase/sticker-15.webp', tags: ['kitchen', 'dapur', 'kakak', 'frozen'] },
  { id: 16, name: 'Chicken Chop Viral', category: 'Makanan', image: '/images/showcase/sticker-16.webp', tags: ['makanan', 'chickenchop', 'western', 'viral'] },
  { id: 17, name: 'Martabak Special', category: 'Makanan', image: '/images/showcase/sticker-17.webp', tags: ['makanan', 'martabak', 'special', 'malaysia'] },
  { id: 18, name: 'Laksa Utara Ori', category: 'Makanan', image: '/images/showcase/sticker-18.webp', tags: ['makanan', 'laksa', 'utara', 'original'] },
  { id: 19, name: 'Nasi Ayam Padu', category: 'Makanan', image: '/images/showcase/sticker-19.webp', tags: ['makanan', 'nasiayam', 'chickenrice', 'padu'] },
  { id: 20, name: 'Ayam Crispy Viral', category: 'Makanan', image: '/images/showcase/sticker-20.webp', tags: ['makanan', 'ayamcrispy', 'friedchicken', 'viral'] },
  { id: 21, name: 'Ayam Gunting Legend', category: 'Makanan', image: '/images/showcase/sticker-21.webp', tags: ['makanan', 'ayamgunting', 'legend', 'viral'] },
  { id: 22, name: 'Satay Kayangan', category: 'Makanan', image: '/images/showcase/sticker-22.webp', tags: ['makanan', 'satay', 'kayangan', 'bbq'] },
  { id: 23, name: 'Burger Leleh Padu', category: 'Makanan', image: '/images/showcase/sticker-23.webp', tags: ['makanan', 'burger', 'leleh', 'padu'] },
  { id: 24, name: 'Sambal Padu Mak Teh', category: 'Makanan', image: '/images/showcase/sticker-24.webp', tags: ['makanan', 'sambal', 'makteh', 'padu'] },
  { id: 25, name: 'Mochi Ice Cream', category: 'Minuman & Dessert', image: '/images/showcase/sticker-25.webp', tags: ['minuman', 'dessert', 'mochi', 'icecream'] },
  { id: 26, name: 'Waffle Meleleh', category: 'Minuman & Dessert', image: '/images/showcase/sticker-26.webp', tags: ['minuman', 'dessert', 'waffle', 'meleleh'] },
  { id: 27, name: 'Jus Buah Segar', category: 'Minuman & Dessert', image: '/images/showcase/sticker-27.webp', tags: ['minuman', 'dessert', 'jus', 'buah'] },
  { id: 28, name: 'Teh Ais Ketagih', category: 'Minuman & Dessert', image: '/images/showcase/sticker-28.webp', tags: ['minuman', 'dessert', 'tehais', 'ketagih'] },
  { id: 29, name: 'Pulut Mangga Heaven', category: 'Minuman & Dessert', image: '/images/showcase/sticker-29.webp', tags: ['minuman', 'dessert', 'pulutmangga', 'heaven'] },
  { id: 30, name: 'Cucur Badak Special', category: 'Snack & Kuih', image: '/images/showcase/sticker-30.webp', tags: ['snack', 'kuih', 'cucurbadak', 'special'] },
  { id: 31, name: 'Kacang Pedas Crunch', category: 'Snack & Kuih', image: '/images/showcase/sticker-31.webp', tags: ['snack', 'kuih', 'kacang', 'pedas'] },
  { id: 32, name: 'Biskut Pandan Delight', category: 'Snack & Kuih', image: '/images/showcase/sticker-32.webp', tags: ['snack', 'kuih', 'biskut', 'pandan'] },
  { id: 33, name: 'Honey Cornflakes Viral', category: 'Snack & Kuih', image: '/images/showcase/sticker-33.webp', tags: ['snack', 'kuih', 'cornflakes', 'honey'] },
  { id: 34, name: 'Cornflakes Madu Special', category: 'Snack & Kuih', image: '/images/showcase/sticker-34.webp', tags: ['snack', 'kuih', 'cornflakes', 'madu'] },
  { id: 35, name: 'Tepung Pelita Opah', category: 'Snack & Kuih', image: '/images/showcase/sticker-35.webp', tags: ['snack', 'kuih', 'tepungpelita', 'opah'] },
];

export const showcaseCategories = [
  'Semua',
  'Bakery',
  'Kitchen',
  'Makanan',
  'Minuman & Dessert',
  'Snack & Kuih',
] as const;
