import React, { useState, useEffect } from 'react';

const BASE = window.basePath || '';

const DEFAULT_MESSAGES = {
    greeting: 'أهلاً بك، هل تبحث عن علاج التصبغات أم حب الشباب؟',
    skincare: 'قسم العناية بالبشرة يضم أحدث المنتجات الطبية والتجميلية.',
    devices: 'أجهزة التجميل المتطورة تجدها في هذا القسم.',
    creams: 'كريمات وسيرومات بخلاصات طبيعية وفعالة.',
    salon: 'تجهيزات الصالونات بأفضل الأسعار وجودة مضمونة.',
    offers: 'عروض وخصومات حصرية لفترة محدودة!',
};

const greetingTemplates = [
    'أهلاً بك في متجر جنين الافتراضي! ماذا تبحث اليوم؟',
    'مرحباً! هل تحتاج مساعدة في العثور على منتج معين؟',
    'أهلاً! يمكنني توجيهك إلى القسم المناسب.',
];

const sectionTemplates = {
    skincare: (count) =>
        count ? `قسم العناية بالبشرة: ${count} منتج متوفر. هل تبحث عن مرطب أم منظف؟` : DEFAULT_MESSAGES.skincare,
    devices: (count) =>
        count ? `قسم الأجهزة: ${count} جهاز تجميلي متطور. جرب أجهزة الليزر والمساج!` : DEFAULT_MESSAGES.devices,
    creams: (count) =>
        count ? `قسم الكريمات والسيرومات: ${count} منتج بخلاصات طبيعية وفعالة.` : DEFAULT_MESSAGES.creams,
    salon: (count) =>
        count ? `تجهيزات الصالونات: ${count} منتج بأفضل الأسعار وجودة مضمونة.` : DEFAULT_MESSAGES.salon,
    offers: (count) =>
        count ? `العروض الخاصة: ${count} منتج مخفض! خصومات حصرية لفترة محدودة. 🎉` : DEFAULT_MESSAGES.offers,
};

export default function AIAssistant({ onNavigate, currentSection, onSectionChange }) {
    const [sectionCounts, setSectionCounts] = useState({});

    useEffect(() => {
        fetch(BASE + '/api/store-3d/shelves')
            .then((r) => r.json())
            .then((res) => {
                const data = res.data || {};
                const counts = {};
                Object.keys(data).forEach((key) => {
                    counts[key] = data[key]?.length || 0;
                });
                setSectionCounts(counts);
            })
            .catch(() => {});
    }, []);
    const [isOpen, setIsOpen] = useState(false);
    const [message, setMessage] = useState(
        greetingTemplates[Math.floor(Math.random() * greetingTemplates.length)]
    );
    const [showOptions, setShowOptions] = useState(true);

    const handleSelectOption = (query) => {
        setShowOptions(false);
        if (query.includes('تصبغات') || query.includes('حب الشباب') || query.includes('بشرة')) {
            setMessage(sectionTemplates.skincare(sectionCounts.skincare));
            onSectionChange('skincare');
        } else if (query.includes('اجهزة') || query.includes('جهاز')) {
            setMessage(sectionTemplates.devices(sectionCounts.devices));
            onSectionChange('devices');
        } else if (query.includes('كريم') || query.includes('سيروم')) {
            setMessage(sectionTemplates.creams(sectionCounts.creams));
            onSectionChange('creams');
        } else if (query.includes('صالون') || query.includes('تجهيز')) {
            setMessage(sectionTemplates.salon(sectionCounts.salon));
            onSectionChange('salon');
        } else if (query.includes('عرض') || query.includes('خصم')) {
            setMessage(sectionTemplates.offers(sectionCounts.offers));
            onSectionChange('offers');
        } else {
            setMessage('يمكنك اختيار أحد الأقسام من القائمة أو استخدام الخريطة للتجول.');
        }
    };

    const resetChat = () => {
        setMessage(greetingTemplates[Math.floor(Math.random() * greetingTemplates.length)]);
        setShowOptions(true);
    };

    return (
        <>
            <button
                className={`ai-avatar ${isOpen ? 'active' : ''}`}
                onClick={() => setIsOpen(!isOpen)}
                title="المساعد الذكي"
            >
                {isOpen ? '✕' : '🤖'}
            </button>

            {isOpen && (
                <div className="ai-chat-window">
                    <div className="ai-chat-header">
                        <span>🤖 المساعد الذكي</span>
                        <button className="ai-chat-reset" onClick={resetChat}>
                            ↻
                        </button>
                    </div>
                    <div className="ai-chat-body">
                        <div className="ai-message">
                            <div className="ai-message-avatar">🤖</div>
                            <div className="ai-message-content">
                                <p>{message}</p>
                            </div>
                        </div>
                        {showOptions && (
                            <div className="ai-options">
                                <button
                                    className="ai-option"
                                    onClick={() => handleSelectOption('تصبغات وبشرة')}
                                >
                                    🧴 علاج التصبغات وحب الشباب
                                </button>
                                <button
                                    className="ai-option"
                                    onClick={() => handleSelectOption('اجهزة')}
                                >
                                    📱 أجهزة التجميل
                                </button>
                                <button
                                    className="ai-option"
                                    onClick={() => handleSelectOption('كريم')}
                                >
                                    🧪 كريمات وسيرومات
                                </button>
                                <button
                                    className="ai-option"
                                    onClick={() => handleSelectOption('صالون')}
                                >
                                    💇 تجهيز الصالونات
                                </button>
                                <button
                                    className="ai-option"
                                    onClick={() => handleSelectOption('عرض')}
                                >
                                    🎉 العروض الخاصة
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </>
    );
}
