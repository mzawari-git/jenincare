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

// Helper to generate shelf positions: 3 columns × 3 rows per section
const SHELF_COLS = 3;
const SHELF_ROWS = 3;
const ROW_HEIGHTS = [0.6, 1.5, 2.4];
let shelfId = 0;

function generateShelves(section, xPositions, zBase) {
  const shelves = [];
  for (let r = 0; r < SHELF_ROWS; r++) {
    for (let c = 0; c < SHELF_COLS; c++) {
      shelfId++;
      shelves.push({
        id: shelfId,
        section,
        position: [xPositions[c], ROW_HEIGHTS[r], zBase],
        rotation: [0, 0, 0],
        productName: 'منتج',
        price: 0,
        rating: 0,
      });
    }
  }
  return shelves;
}

export const PRODUCT_SHELVES = [
  // Skincare — 3 cols on left side
  ...generateShelves('skincare', [-1.6, -1.1, -0.6], 1.6),
  // Devices — 3 cols on right side
  ...generateShelves('devices', [0.6, 1.1, 1.6], 1.6),
  // Creams — 3 cols on left side
  ...generateShelves('creams', [-1.6, -1.1, -0.6], -0.3),
  // Salon — 3 cols on right side
  ...generateShelves('salon', [0.6, 1.1, 1.6], -0.3),
  // Offers — 3 cols center
  ...generateShelves('offers', [-0.5, 0, 0.5], -1.7),
];

export const SECTION_NAMES = SECTIONS.reduce((acc, s) => {
    acc[s.id] = s;
    return acc;
}, {});
