export const SECTIONS = [
    {
        id: 'entrance',
        name_ar: 'المدخل',
        name_en: 'Entrance',
        color: '#8B5CF6',
        position: [0, 0, 2.0],
        size: [3, 4, 1],
        isWalkable: true,
    },
    {
        id: 'skincare',
        name_ar: 'العناية بالبشرة',
        name_en: 'Skincare',
        color: '#EC4899',
        position: [-1.1, 0, 0.4],
        size: [1.8, 4, 1.8],
        isWalkable: true,
    },
    {
        id: 'devices',
        name_ar: 'أجهزة التجميل',
        name_en: 'Beauty Devices',
        color: '#3B82F6',
        position: [1.1, 0, 0.4],
        size: [1.8, 4, 1.8],
        isWalkable: true,
    },
    {
        id: 'creams',
        name_ar: 'الكريمات والسيرومات',
        name_en: 'Creams & Serums',
        color: '#F59E0B',
        position: [-1.1, 0, -1.3],
        size: [1.8, 4, 1.4],
        isWalkable: true,
    },
    {
        id: 'salon',
        name_ar: 'تجهيز الصالونات',
        name_en: 'Salon Equipment',
        color: '#10B981',
        position: [1.1, 0, -1.3],
        size: [1.8, 4, 1.4],
        isWalkable: true,
    },
    {
        id: 'offers',
        name_ar: 'العروض الخاصة',
        name_en: 'Special Offers',
        color: '#EF4444',
        position: [0, 0, -2.25],
        size: [1.6, 4, 0.5],
        isWalkable: true,
    },
];

export const PRODUCT_SHELVES = [
    // Skincare — back wall at Z≈1.8 (2.5-0.7), shelves in front at Z≈1.6
    { id: 1, section: 'skincare', position: [-1.6, 1.2, 1.6], rotation: [0, 0, 0], productName: 'غسول وجه', price: 89, rating: 4.5 },
    { id: 2, section: 'skincare', position: [-1.1, 1.2, 1.6], rotation: [0, 0, 0], productName: 'تونر', price: 65, rating: 4.2 },
    { id: 3, section: 'skincare', position: [-0.6, 1.2, 1.6], rotation: [0, 0, 0], productName: 'مقشر', price: 79, rating: 4.7 },
    // Devices — back wall at Z≈1.8
    { id: 4, section: 'devices', position: [0.6, 1.2, 1.6], rotation: [0, 0, 0], productName: 'جهاز ليزر', price: 1299, rating: 4.8 },
    { id: 5, section: 'devices', position: [1.1, 1.2, 1.6], rotation: [0, 0, 0], productName: 'جهاز مساج', price: 349, rating: 4.3 },
    { id: 6, section: 'devices', position: [1.6, 1.2, 1.6], rotation: [0, 0, 0], productName: 'مشط أيوني', price: 199, rating: 4.1 },
    // Creams — back wall at Z≈-0.1 (2.5-2.6), shelves at Z≈-0.3
    { id: 7, section: 'creams', position: [-1.6, 1.2, -0.3], rotation: [0, 0, 0], productName: 'كريم ترطيب', price: 129, rating: 4.9 },
    { id: 8, section: 'creams', position: [-1.1, 1.2, -0.3], rotation: [0, 0, 0], productName: 'سيروم فيتامين سي', price: 159, rating: 4.7 },
    { id: 9, section: 'creams', position: [-0.6, 1.2, -0.3], rotation: [0, 0, 0], productName: 'كريم ليلي', price: 99, rating: 4.4 },
    // Salon — back wall at Z≈-0.1
    { id: 10, section: 'salon', position: [0.6, 1.2, -0.3], rotation: [0, 0, 0], productName: 'كرسي صالون', price: 2499, rating: 4.5 },
    { id: 11, section: 'salon', position: [1.1, 1.2, -0.3], rotation: [0, 0, 0], productName: 'مجفف شعر', price: 399, rating: 4.2 },
    { id: 12, section: 'salon', position: [1.6, 1.2, -0.3], rotation: [0, 0, 0], productName: 'عدسة مكبرة', price: 149, rating: 4.0 },
    // Offers — back wall at Z≈-1.5 (2.5-4.0), shelves at Z≈-1.7
    { id: 13, section: 'offers', position: [-0.5, 1.2, -1.7], rotation: [0, 0, 0], productName: 'عرض العناية الكامل', price: 299, rating: 4.9 },
    { id: 14, section: 'offers', position: [0, 1.2, -1.7], rotation: [0, 0, 0], productName: 'عرض الأجهزة', price: 899, rating: 4.6 },
    { id: 15, section: 'offers', position: [0.5, 1.2, -1.7], rotation: [0, 0, 0], productName: 'عرض الصالون', price: 1499, rating: 4.8 },
];

export const SECTION_NAMES = SECTIONS.reduce((acc, s) => {
    acc[s.id] = s;
    return acc;
}, {});
