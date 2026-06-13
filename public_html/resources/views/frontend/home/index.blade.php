∩╗┐@extends($layoutPath)

@section('title', ($siteSettings['site_name'] ?? '╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä') . ' | ┘à┘å╪╡╪⌐ ╪º┘ä╪¼┘à╪º┘ä ╪º┘ä╪░┘â┘è╪⌐')
@section('meta_description', '╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä - ┘ê╪¼┘ç╪¬┘â ╪º┘ä╪ú┘ê┘ä┘ë ┘ä┘ä╪╣┘å╪º┘è╪⌐ ╪¿╪º┘ä╪¿╪┤╪▒╪⌐ ┘ê╪º┘ä╪┤╪╣╪▒. ┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ 100%╪î ╪┤╪¡┘å ┘ä┘â┘ä ┘ü┘ä╪│╪╖┘è┘å╪î ╪»┘ü╪╣ ╪╣┘å╪» ╪º┘ä╪º╪│╪¬┘ä╪º┘à╪î ┘ê╪»╪╣┘à ╪º╪¡╪¬╪▒╪º┘ü┘è.')
@section('meta_keywords', '╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä, ╪¼┘è┘å┘è ┘â┘è╪▒, ╪╣┘å╪º┘è╪⌐ ╪¿╪º┘ä╪¿╪┤╪▒╪⌐, ╪╣┘å╪º┘è╪⌐ ╪¿╪º┘ä╪┤╪╣╪▒, ┘à┘å╪¬╪¼╪º╪¬ ╪¬╪¼┘à┘è┘ä, ┘ü┘ä╪│╪╖┘è┘å, ╪┤╪¡┘å ┘à╪¼╪º┘å┘è, ┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐, ╪¼┘à╪º┘ä, ╪░┘â╪º╪í ╪º╪╡╪╖┘å╪º╪╣┘è')

@push('scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "{{ $siteSettings['site_name'] ?? '╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä' }}",
  "url": "{{ url('/') }}",
  "logo": "{{ asset('assets/images/logo.png') }}",
  "description": "┘à┘å╪╡╪⌐ ╪º┘ä╪╣┘å╪º┘è╪⌐ ╪¿╪º┘ä╪¿╪┤╪▒╪⌐ ╪º┘ä╪░┘â┘è╪⌐ - ┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ 100%",
  "address": { "@type": "PostalAddress", "addressLocality": "╪▒╪º┘à ╪º┘ä┘ä┘ç", "addressCountry": "PS" },
  "contactPoint": { "@type": "ContactPoint", "telephone": "{{ $siteSettings['site_phone'] ?? '+972 56 903 0203' }}", "contactType": "customer service" },
  "sameAs": ["{{ $siteSettings['facebook_url'] ?? '#' }}", "{{ $siteSettings['instagram_url'] ?? '#' }}"]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{ $siteSettings['site_name'] ?? '╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä' }}",
  "url": "{{ url('/') }}",
  "potentialAction": { "@type": "SearchAction", "target": "{{ url('/shop') }}?search={search_term_string}", "query-input": "required name=search_term_string" }
}
</script>
@endpush

@section('content')

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
     SECTION 1: HERO ΓÇö Dynamic Title + Product Slideshow
     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
@php
$allPhrases = [
    '┘ä┘à╪│╪º╪¬ ╪│╪º╪¡╪▒╪⌐ ╪¬╪¿╪»╪ú ╪¿┘à┘å╪¬╪¼╪º╪¬ ╪º╪│╪¬╪½┘å╪º╪ª┘è╪⌐... ╪º╪«╪¬╪º╪▒┘è ╪º┘ä╪ú┘ü╪╢┘ä ┘à╪╣ ╪¼┘å┘è┘å.',
    '╪¼┘ê╪»╪⌐ ┘ä╪º ╪¬┘Å╪╢╪º┘ç┘ë ┘ä╪¼┘à╪º┘ä ┘è╪»┘ê┘à... ┘à╪│╪¬╪¡╪╢╪▒╪º╪¬ ╪╡┘Å┘à┘à╪¬ ┘ä╪¬╪¿╪▒╪▓ ╪Ñ╪┤╪▒╪º┘é╪¬┘â┘É.',
    '╪ú┘ä┘ê╪º┘å ╪║┘å┘è╪⌐ ┘ê╪¬╪▒┘â┘è╪¿╪º╪¬ ╪ó┘à┘å╪⌐╪î ┘ä╪¬╪¼╪▒╪¿╪⌐ ╪¼┘à╪º┘ä ╪¬┘ü┘ê┘é ╪º┘ä╪¬┘ê┘é╪╣╪º╪¬.',
    '╪│╪▒ ╪º┘ä╪Ñ╪╖┘ä╪º┘ä╪⌐ ╪º┘ä┘à╪½╪º┘ä┘è╪⌐ ┘è╪¿╪»╪ú ┘à┘å ┘ç┘å╪º... ╪»╪╣┘è ╪º┘ä╪¼┘ê╪»╪⌐ ╪¬╪¬╪¡╪»╪½ ╪╣┘å┘â┘É.',
    '┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ 100%... ┘ä╪ú┘å ╪¿╪┤╪▒╪¬┘â ╪¬╪│╪¬╪¡┘é ╪º┘ä╪ú┘ü╪╢┘ä ╪»╪º╪ª┘à╪º┘ï.',
    '╪ú╪¡╪»╪½ ╪º┘ä╪¬┘é┘å┘è╪º╪¬ ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐ ╪¿┘è┘å ┘è╪»┘è┘â┘É╪î ┘ä┘å╪¬╪º╪ª╪¼ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐ ╪¬╪¿┘ç╪▒ ╪╣┘à┘ä╪º╪ª┘â┘É.',
    '╪º╪▒╪¬┘é┘è ╪¿┘à╪│╪¬┘ê┘ë ╪«╪»┘à╪º╪¬┘â┘É ┘à╪╣ ╪ú╪¼┘ç╪▓╪⌐ ╪º┘ä╪¼┘è┘ä ╪º┘ä╪¼╪»┘è╪» ┘à┘å ╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä.',
    '╪»┘é╪⌐ ╪º┘ä╪ú╪»╪º╪í╪î ┘ê╪│╪▒╪╣╪⌐ ╪º┘ä┘å╪¬╪º╪ª╪¼... ╪º┘ä╪¬┘â┘å┘ê┘ä┘ê╪¼┘è╪º ╪º┘ä╪░┘â┘è╪⌐ ┘ü┘è ╪«╪»┘à╪⌐ ╪º┘ä╪¼┘à╪º┘ä.',
    '╪º╪│╪¬╪½┘à╪▒┘è ┘ü┘è ┘å╪¼╪º╪¡┘â┘É ┘à╪╣ ╪ú╪¼┘ç╪▓╪⌐ ╪╡┘Å┘à┘à╪¬ ┘ä╪¬╪»┘ê┘à ┘ê╪¬┘é╪»┘à ╪º┘ä╪ú┘ü╪╢┘ä.',
    '┘à┘å ╪º┘ä╪¬╪╡┘à┘è┘à ╪Ñ┘ä┘ë ╪º┘ä╪¬┘å┘ü┘è╪░... ┘å╪¼┘ç╪▓ ╪╡╪º┘ä┘ê┘å┘â┘É ┘ä┘è┘â┘ê┘å ┘ê╪¼┘ç╪⌐ ╪º┘ä┘ü╪«╪º┘à╪⌐ ╪º┘ä╪ú┘ê┘ä┘ë.',
    '╪ú╪½╪º╪½ ╪╣╪╡╪▒┘è ┘ê┘à╪╣╪»╪º╪¬ ┘à╪¬┘â╪º┘à┘ä╪⌐╪î ┘å╪¿┘å┘è ┘ä┘â┘É ┘à╪│╪º╪¡╪⌐ ╪¬╪╣┘â╪│ ╪▒┘é┘è ╪ú╪╣┘à╪º┘ä┘â┘É.',
    '╪▒╪º╪¡╪⌐ ┘ä╪╣┘à┘ä╪º╪ª┘â┘É ┘ê╪¬┘à┘è╪▓ ┘ä┘à╪┤╪▒┘ê╪╣┘â┘É╪î ┘à╪╣ ╪¡┘ä┘ê┘ä ╪¼┘å┘è┘å ╪º┘ä╪┤╪º┘à┘ä╪⌐ ┘ä╪¬╪¼┘ç┘è╪▓ ╪º┘ä╪╡╪º┘ä┘ê┘å╪º╪¬.',
    '┘ä╪º ╪¬╪│╪º┘ê┘à┘è ╪╣┘ä┘ë ╪ú┘å╪º┘é╪⌐ ┘à┘â╪º┘å┘â┘É... ╪»╪╣┘è┘å╪º ┘å╪╡┘å╪╣ ┘ä┘â┘É ╪╡╪º┘ä┘ê┘å ╪ú╪¡┘ä╪º┘à┘â┘É.',
    '╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä: ╪«┘è╪º╪▒ ╪º┘ä┘à╪¡╪¬╪▒┘ü┘è┘å ╪º┘ä╪ú┘ê┘ä.',
    '┘â┘ä ┘à╪º ┘è╪«╪╡ ╪╣╪º┘ä┘à ╪º┘ä╪¼┘à╪º┘ä ┘ê╪º┘ä╪ú┘å╪º┘é╪⌐... ╪¬╪¡╪¬ ╪│┘é┘ü ┘ê╪º╪¡╪».',
    '╪┤╪▒┘è┘â┘â┘É ╪º┘ä┘à┘ê╪½┘ê┘é ┘ä╪▒╪¡┘ä╪⌐ ┘å╪¼╪º╪¡ ┘ê╪¬╪ú┘ä┘é ┘à╪│╪¬┘à╪▒╪⌐.',
    '╪¼┘ê╪»╪⌐ ┘å╪½┘é ╪¿┘ç╪º╪î ┘ê╪«╪»┘à╪⌐ ╪¬┘ä╪¿┘è ╪¬╪╖┘ä╪╣╪º╪¬┘â┘à.',
    '╪º┘â╪¬╪┤┘ü┘è ╪ú╪│╪▒╪º╪▒ ╪º┘ä╪¼┘à╪º┘ä ┘à╪╣ ╪ú┘ü╪«╪▒ ╪º┘ä┘à╪º╪▒┘â╪º╪¬ ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐ ╪º┘ä╪ú╪╡┘ä┘è╪⌐.',
    '╪╡╪º┘ä┘ê┘å┘â┘É ╪º┘ä┘à╪¬┘â╪º┘à┘ä... ┘à┘å ╪º┘ä┘ü┘â╪▒╪⌐ ╪Ñ┘ä┘ë ╪º┘ä┘ê╪º┘é╪╣ ┘à╪╣ ╪«╪¿╪▒╪º╪í ╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä.',
    '┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐╪î ╪ú╪¼┘ç╪▓╪⌐ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐╪î ╪¬╪¼┘ç┘è╪▓ ┘à╪¬┘â╪º┘à┘ä... ┘ü┘è ┘à┘â╪º┘å ┘ê╪º╪¡╪».',
    '╪Ñ╪┤╪▒╪º┘é╪⌐ ┘ê╪▒╪»┘è╪⌐ ╪¬┘ä┘ü╪¬ ╪º┘ä╪ú┘å╪╕╪º╪▒.. ╪¿┘ä┘à╪│╪º╪¬ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐ ┘à┘å ╪¼┘å┘è┘å.',
    '╪»╪╣┘è ╪¼┘à╪º┘ä┘â ┘è╪¬╪ú┘ä┘é ╪¿┘å╪╣┘ê┘à╪⌐ ┘ê╪▒┘é┘è ┘ä╪º ┘à╪½┘è┘ä ┘ä┘ç┘à╪º.',
    '╪▒┘ü╪º┘ç┘è╪⌐ ╪º┘ä╪¼┘à╪º┘ä ┘ü┘è ┘â┘ä ╪¬┘ü╪╡┘è┘ä╪î ┘ä╪¬┘â┘ê┘å┘è ╪º┘ä╪ú╪¼┘à┘ä ╪»╪º╪ª┘à╪º┘ï.',
    '╪¬╪▒┘â┘è╪¿╪º╪¬ ╪║┘å┘è╪⌐ ┘ê╪╣╪╡╪▒┘è╪⌐ ╪¬╪¿╪▒╪▓ ┘à┘ä╪º┘à╪¡┘â ╪¿╪ú┘å╪º┘é╪⌐ ╪│╪º╪¡╪▒╪⌐.',
    '╪¼╪º╪░╪¿┘è╪⌐ ╪¬┘å╪¿╪╢ ╪¿╪º┘ä╪¡┘è╪º╪⌐.. ┘ä╪ú┘å┘â┘É ╪ú┘è┘é┘ê┘å╪⌐ ╪º┘ä╪¼┘à╪º┘ä ╪º┘ä┘à╪│╪¬┘à╪▒.',
    '╪¬┘â┘å┘ê┘ä┘ê╪¼┘è╪º ┘à╪¬╪╖┘ê╪▒╪⌐ ╪¬╪▒╪│┘à ┘à╪│╪¬┘é╪¿┘ä ╪╡╪º┘ä┘ê┘å┘â ╪¿╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐ ╪╣╪º┘ä┘è╪⌐.',
    '╪ú╪»╪º╪í ╪º╪│╪¬╪½┘å╪º╪ª┘è ┘è╪╢┘à┘å ┘ä╪╣┘à┘ä╪º╪ª┘â ╪¬╪¼╪▒╪¿╪⌐ ┘ä╪º ╪¬┘Å┘å╪│┘ë.',
    '┘ä╪ú┘å ╪º┘ä╪¬┘à┘è╪▓ ┘ç╪»┘ü┘â.. ┘ê╪╢╪╣┘å╪º ╪¿┘è┘å ┘è╪»┘è┘â ╪ú╪¡╪»╪½ ╪ú╪¼┘ç╪▓╪⌐ ╪º┘ä╪¬╪¼┘à┘è┘ä ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐.',
    '╪»┘é╪⌐ ╪º┘ä╪º╪¿╪¬┘â╪º╪▒ ┘ä┘å╪¬╪º╪ª╪¼ ┘à╪¿┘ç╪▒╪⌐ ╪¬╪╣╪▓╪▓ ╪½┘é╪⌐ ╪╣┘à┘ä╪º╪ª┘â ┘è┘ê┘à╪º┘ï ╪¿╪╣╪» ┘è┘ê┘à.',
    '╪º╪│╪¬╪½┘à╪▒┘è ┘ü┘è ╪º┘ä┘é┘à╪⌐.. ╪ú╪¼┘ç╪▓╪⌐ ┘à╪╡┘à┘à╪⌐ ┘ä╪▒┘ê╪º╪» ╪╣╪º┘ä┘à ╪º┘ä╪¬╪¼┘à┘è┘ä.',
    '╪ú╪½╪º╪½ ┘è╪¼┘à╪╣ ╪¿┘è┘å ╪º┘ä╪▒┘ü╪º┘ç┘è╪⌐ ┘ê╪º┘ä╪╣┘à┘ä┘è╪⌐.. ┘ä╪╡╪º┘ä┘ê┘å ┘è┘å╪¿╪╢ ╪¿╪º┘ä┘ü╪«╪º┘à╪⌐.',
    '┘å╪╡┘à┘à ┘à╪│╪º╪¡╪¬┘â ╪¿╪▒╪ñ┘è╪⌐ ╪╣╪╡╪▒┘è╪⌐ ╪¬╪╣┘â╪│ ┘ç┘ê┘è╪⌐ ╪╣┘ä╪º┘à╪¬┘â ╪º┘ä╪¬╪¼╪º╪▒┘è╪⌐.',
    '╪▒╪º╪¡╪⌐ ┘à╪╖┘ä┘é╪⌐ ┘ê╪¬╪╡┘à┘è┘à ┘å┘é┘è ┘è┘ä┘ç┘à ┘â┘ä ┘à┘å ┘è╪▓┘ê╪▒ ╪╡╪º┘ä┘ê┘å┘â.',
    '┘à┘å ╪º┘ä┘ü┘â╪▒╪⌐ ╪Ñ┘ä┘ë ╪º┘ä╪¬╪ú┘ä┘é.. ╪¬╪¼┘ç┘è╪▓╪º╪¬ ┘à╪¬┘â╪º┘à┘ä╪⌐ ┘ä╪¿┘è╪ª╪⌐ ╪╣┘à┘ä ╪Ñ╪¿╪»╪º╪╣┘è╪⌐ ┘ê┘à╪▒┘è╪¡╪⌐.',
    '╪º╪¼╪╣┘ä┘è ╪╡╪º┘ä┘ê┘å┘â ╪¬╪¡┘ü╪⌐ ┘ü┘å┘è╪⌐ ╪¬╪¬╪¡╪»╪½ ╪╣┘å ╪▒┘é┘è ╪º╪«╪¬┘è╪º╪▒╪º╪¬┘â.',
    '╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä.. ╪¿╪╡┘à╪¬┘â ╪º┘ä╪«╪º╪╡╪⌐ ┘ü┘è ╪╣╪º┘ä┘à ╪º┘ä╪ú┘å╪º┘é╪⌐ ┘ê╪º┘ä╪º╪¡╪¬╪▒╪º┘ü.',
    '┘å╪¼┘à╪╣ ┘ä┘â ╪ú╪│╪▒╪º╪▒ ╪º┘ä╪¼┘à╪º┘ä ┘ê╪º┘ä╪¬╪¼┘ç┘è╪▓ ╪º┘ä╪º╪¡╪¬╪▒╪º┘ü┘è ┘ü┘è ┘à┘â╪º┘å ┘ê╪º╪¡╪».',
    '╪¼┘ê╪»╪⌐ ╪¬╪¬╪¡╪»╪½ ╪╣┘å ┘å┘ü╪│┘ç╪º.. ┘ê╪¬┘ü╪º╪╡┘è┘ä ╪»┘é┘è┘é╪⌐ ╪¬╪╡┘å╪╣ ╪º┘ä┘ü╪º╪▒┘é.',
    '╪▒┘ê╪º╪ª╪╣ ╪º┘ä╪¬╪¼┘à┘è┘ä ┘ê╪º┘ä╪¬╪¼┘ç┘è╪▓╪º╪¬.. ┘ä┘å╪¼╪º╪¡ ┘ê╪¬╪ú┘ä┘é ┘ä╪º ┘è╪╣╪▒┘ü ╪º┘ä╪¡╪»┘ê╪».',
    '┘ê╪º╪¼┘ç╪¬┘â ╪º┘ä╪ú┘ê┘ä┘ë ┘ä┘â┘ä ┘à╪º ┘è╪¿╪▒╪▓ ╪º┘ä╪¼┘à╪º┘ä ┘ê┘è╪▒╪¬┘é┘è ╪¿╪ú╪╣┘à╪º┘ä┘â ╪Ñ┘ä┘ë ╪º┘ä┘é┘à╪⌐.',
    // ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ NEW MARKETING PHRASES ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
    '╪º┘â╪¬╪┤┘ü┘è ╪╣╪º┘ä┘à ╪º┘ä╪¼┘à╪º┘ä ╪¿╪ú┘ü╪╢┘ä ╪º┘ä┘à╪º╪▒┘â╪º╪¬ ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐ ╪º┘ä╪ú╪╡┘ä┘è╪⌐.',
    '╪¿╪┤╪▒╪¬┘â┘É ╪¬╪│╪¬╪¡┘é ╪º┘ä╪ú┘ü╪╢┘ä... ┘à╪│╪¬╪¡╪╢╪▒╪º╪¬ ╪╖╪¿┘è╪╣┘è╪⌐ ╪¿┘å╪¬╪º╪ª╪¼ ┘à╪╢┘à┘ê┘å╪⌐.',
    '╪¬╪ú┘ä┘é┘è ╪¿╪½┘é╪⌐ ┘à╪╣ ┘à┘å╪¬╪¼╪º╪¬ ╪º╪«╪¬╪º╪▒┘ç╪º ╪«╪¿╪▒╪º╪í ╪º┘ä╪¼┘à╪º┘ä ╪¿╪╣┘å╪º┘è╪⌐ ┘ü╪º╪ª┘é╪⌐.',
    '┘â┘ä ┘ä┘à╪│╪⌐ ╪¬┘å╪¿╪╢ ╪¿╪º┘ä╪¡┘è╪º╪⌐... ╪¼┘à╪º┘ä┘â┘É ┘è╪¿╪»╪ú ┘à┘å ╪º╪«╪¬┘è╪º╪▒╪º╪¬┘â┘É ╪º┘ä╪░┘â┘è╪⌐.',
    '╪¡┘ê┘ä┘è ╪▒┘ê╪¬┘è┘å ╪º┘ä╪╣┘å╪º┘è╪⌐ ╪º┘ä┘è┘ê┘à┘è ╪Ñ┘ä┘ë ┘ä╪¡╪╕╪º╪¬ ╪│╪¡╪▒┘è╪⌐ ┘à┘å ╪º┘ä╪º╪│╪¬╪▒╪«╪º╪í.',
    '┘à┘å╪¬╪¼╪º╪¬ ╪╡┘Å┘å╪╣╪¬ ╪¿╪¡╪¿╪î ┘ä╪¬╪▒╪│┘à ╪º╪¿╪¬╪│╪º┘à╪⌐ ╪º┘ä╪½┘é╪⌐ ╪╣┘ä┘ë ┘ê╪¼┘ç┘â┘É ┘â┘ä ┘è┘ê┘à.',
    '┘ä╪ú┘å┘â┘É ╪¬╪│╪¬╪¡┘é┘è┘å ╪º┘ä╪ú┘ü╪╢┘ä... ┘å┘é╪»┘à ┘ä┘â┘É ╪¬╪¼╪▒╪¿╪⌐ ╪¬╪│┘ê┘é ╪º╪│╪¬╪½┘å╪º╪ª┘è╪⌐.',
    '┘à╪╣ ╪¼┘å┘è┘å╪î ┘â┘ä ┘è┘ê┘à ┘ç┘ê ┘è┘ê┘à ╪¼┘à╪º┘ä... ╪º┘â╪¬╪┤┘ü┘è ╪│╪▒ ╪º┘ä╪Ñ╪┤╪▒╪º┘é╪⌐.',
    '┘ä┘à╪│╪⌐ ┘ê╪º╪¡╪»╪⌐ ╪¬┘â┘ü┘è ┘ä╪¬╪║┘è┘è╪▒ ┘â┘ä ╪┤┘è╪í... ╪º╪«╪¬╪º╪▒┘è ╪º┘ä╪░┘â╪º╪í ┘ü┘è ╪º┘ä╪¼┘à╪º┘ä.',
    '┘å╪ñ┘à┘å ╪¿╪ú┘å ╪º┘ä╪¼┘à╪º┘ä ╪º┘ä╪¡┘é┘è┘é┘è ┘è╪¿╪»╪ú ┘à┘å ╪º┘ä╪»╪º╪«┘ä... ┘ê┘å┘â┘à┘ä┘ç ┘à┘å ╪º┘ä╪«╪º╪▒╪¼.',
    '╪¬╪│┘ê┘é┘è ╪¿╪░┘â╪º╪í╪î ╪¬╪ú┘ä┘é┘è ╪¿╪½┘é╪⌐... ╪¼┘å┘è┘å ┘ê╪¼┘ç╪¬┘â┘É ╪º┘ä╪ú┘ê┘ä┘ë ┘ä┘ä╪ú┘å╪º┘é╪⌐.',
    '┘à╪│╪¬╪¡╪╢╪▒╪º╪¬ ┘ü╪º╪«╪▒╪⌐ ╪¿╪ú╪│╪╣╪º╪▒ ╪¬┘å╪º┘ü╪│┘è╪⌐... ╪¼┘à╪º┘ä┘â┘É ┘ä┘à ┘è╪╣╪» ╪¡┘ä┘à╪º┘ï.',
    '┘å┘é┘ä╪¿ ╪º┘ä┘à┘ê╪º╪▓┘è┘å ┘ü┘è ╪╣╪º┘ä┘à ╪º┘ä╪¬╪¼┘à┘è┘ä... ╪¼┘ê╪»╪⌐ ╪╣╪º┘ä┘à┘è╪⌐ ╪¿╪«╪»┘à╪⌐ ┘à╪¡┘ä┘è╪⌐.',
    '┘à╪╣ ┘â┘ä ╪╖┘ä╪¿╪î ┘å╪╣╪»┘â┘É ╪¿╪¬╪¼╪▒╪¿╪⌐ ┘ä╪º ╪¬┘Å┘å╪│┘ë... ┘à┘å ╪º┘ä╪º╪«╪¬┘è╪º╪▒ ╪¡╪¬┘ë ╪º┘ä╪¬┘ê╪╡┘è┘ä.',
    '┘ä┘à╪│╪⌐ ┘å╪º╪╣┘à╪⌐╪î ╪╣╪╖╪▒ ┘è╪»┘ê┘à╪î ╪¼┘à╪º┘ä ┘è╪¬╪¼╪»╪»... ╪¼┘å┘è┘å ╪¬┘ü┘ç┘à┘â┘É.',
    '╪º╪¡╪╡┘ä┘è ╪╣┘ä┘ë ╪Ñ╪╖┘ä╪º┘ä╪⌐ ╪º┘ä┘å╪¼┘à╪º╪¬ ┘à╪╣ ┘à┘å╪¬╪¼╪º╪¬ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐ ┘ü┘è ┘à┘å╪▓┘ä┘â┘É.',
    '┘å╪¿╪¬┘â╪▒ ┘ä┘â┘É ╪º┘ä╪¡┘ä┘ê┘ä... ┘ä╪¬╪¿╪»╪╣┘è ╪ú┘å╪¬┘É ┘ü┘è ╪º┘ä╪¼┘à╪º┘ä ┘ê╪º┘ä╪ú┘å╪º┘é╪⌐.',
    '╪¼┘à╪º┘ä┘â┘É ┘ç┘ê ╪ú┘ê┘ä┘ê┘è╪¬┘å╪º... ┘ê╪▒╪╢╪º┘â┘É ┘ç┘ê ┘à┘â╪│╪¿┘å╪º ╪º┘ä╪ú┘â╪¿╪▒.',
    '╪º╪«╪¬┘è╪º╪▒┘â┘É ┘ä┘Ç ╪¼┘å┘è┘å = ╪º╪«╪¬┘è╪º╪▒ ┘ä┘ä╪½┘é╪⌐ ┘ê╪º┘ä╪¼┘ê╪»╪⌐ ┘ê╪º┘ä╪ú╪╡╪º┘ä╪⌐.',
    '┘å╪│╪º┘ü╪▒ ╪¡┘ê┘ä ╪º┘ä╪╣╪º┘ä┘à ┘ä┘å╪¼┘ä╪¿ ┘ä┘â┘É ╪ú╪¡╪»╪½ ┘à╪º ╪¬┘ê╪╡┘ä ╪Ñ┘ä┘è┘ç ╪╣┘ä┘à ╪º┘ä╪¬╪¼┘à┘è┘ä.',
    '╪╡╪º┘ä┘ê┘å┘â┘É ┘è╪│╪¬╪¡┘é ╪º┘ä╪ú┘ü╪╢┘ä... ┘å╪¼┘ç╪▓┘ç ┘ä┘â┘É ╪¿╪ú╪╣┘ä┘ë ┘à╪╣╪º┘è┘è╪▒ ╪º┘ä┘ü╪«╪º┘à╪⌐.',
    '╪ú╪¼┘ç╪▓╪⌐ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐ ╪¬╪╢┘à┘å ┘ä┘â┘É ┘å╪¬╪º╪ª╪¼ ┘à╪░┘ç┘ä╪⌐ ┘ü┘è ┘â┘ä ╪º╪│╪¬╪«╪»╪º┘à.',
    '┘å╪¿┘å┘è ┘ä┘â┘É ┘é╪╡╪⌐ ┘å╪¼╪º╪¡... ╪¬╪¿╪»╪ú ╪¿╪╡╪º┘ä┘ê┘å ┘à╪¬┘â╪º┘à┘ä ┘ê╪¼┘à╪º┘ä ┘ä╪º ╪¡╪»┘ê╪» ┘ä┘ç.',
    '╪º╪│╪¬╪½┘à╪▒┘è ┘ü┘è ╪¼┘à╪º┘ä┘â┘É ╪º┘ä┘è┘ê┘à╪î ┘ê╪º╪¼┘å┘è ╪º┘ä╪½┘å╪º╪í ╪║╪»╪º┘ï ┘à╪╣ ╪¼┘å┘è┘å.',
    '┘ä┘à╪│╪⌐ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐ ╪¬┘Å╪¡╪»╪½ ┘ü╪▒┘é╪º┘ï ┘â╪¿┘è╪▒╪º┘ï... ╪º┘â╪¬╪┤┘ü┘è ╪│╪▒ ╪º┘ä╪¬╪ú┘ä┘é.',
    '┘å╪¡┘å┘Å ┘ä╪│┘å╪º ┘à╪¼╪▒╪» ┘à╪¬╪¼╪▒... ┘å╪¡┘å┘Å ┘ê╪¼┘ç╪¬┘â┘É ╪º┘ä╪┤╪º┘à┘ä╪⌐ ┘ä╪╣╪º┘ä┘à ╪º┘ä╪¼┘à╪º┘ä.',
    '┘â┘ä ┘à┘å╪¬╪¼ ┘è╪▒┘ê┘è ┘é╪╡╪⌐... ┘é╪╡╪⌐ ╪¼┘ê╪»╪⌐ ┘ê╪ú╪╡╪º┘ä╪⌐ ┘ê╪¬┘à┘è╪▓ ┘à┘å ╪¼┘å┘è┘å.',
    '╪¿╪┤╪▒╪⌐ ┘å╪╢╪▒╪⌐╪î ╪┤╪╣╪▒ ╪╡╪¡┘è╪î ╪Ñ╪╖┘ä╪º┘ä╪⌐ ╪│╪º╪¡╪▒╪⌐... ┘â┘ä ┘ç╪░╪º ┘ê╪ú┘â╪½╪▒ ┘à╪╣ ╪¼┘å┘è┘å.',
    '┘å╪¡┘ê┘æ┘ä ╪¡┘ä┘à ╪º┘ä╪¼┘à╪º┘ä ╪Ñ┘ä┘ë ┘ê╪º┘é╪╣ ┘à┘ä┘à┘ê╪│... ┘à╪╣ ┘à┘å╪¬╪¼╪º╪¬ ┘à┘ê╪½┘ê┘é╪⌐ ┘ê┘à╪╢┘à┘ê┘å╪⌐.',
    '╪¬╪│┘ê┘é┘è ╪º┘ä╪ó┘å╪î ┘ê╪º╪¿╪»╪ª┘è ╪▒╪¡┘ä╪¬┘â┘É ┘å╪¡┘ê ╪¼┘à╪º┘ä ┘ä╪º ┘è┘é╪º┘ê┘à.',
    '╪¼┘å┘è┘å... ╪¡┘è╪½ ╪¬┘ä╪¬┘é┘è ╪º┘ä╪ú╪¡┘ä╪º┘à ╪¿╪º┘ä╪¡┘é┘è┘é╪⌐ ┘ü┘è ╪╣╪º┘ä┘à ╪º┘ä╪¬╪¼┘à┘è┘ä.',
    '╪¬╪ú┘ä┘é┘è ╪»╪º╪ª┘à╪º┘ï╪î ┘à╪╣ ┘à┘å╪¬╪¼╪º╪¬ ╪º╪«╪¬╪▒┘å╪º┘ç╪º ┘ä┘â┘É ╪¿┘â┘ä ╪¡╪¿ ┘ê╪»┘é╪⌐.',
    '╪º╪«╪¬╪º╪▒┘è ╪º┘ä╪░┘â╪º╪í╪î ╪º╪«╪¬╪º╪▒┘è ╪º┘ä╪¼┘ê╪»╪⌐╪î ╪º╪«╪¬╪º╪▒┘è ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä.',
    '┘å╪╣┘è╪» ╪¬╪╣╪▒┘è┘ü ╪º┘ä╪¼┘à╪º┘ä ┘ü┘è ┘ü┘ä╪│╪╖┘è┘å... ╪¼┘ê╪»╪⌐ ╪╣╪º┘ä┘à┘è╪⌐ ╪¿╪ú┘è╪»┘ì ┘à╪¡┘ä┘è╪⌐.',
];

// Hero two-line headlines (independent from product slides)
$heroHeadlines = [
    ['line1' => '┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ 100%', 'line2' => '╪¼┘à╪º┘ä ┘ä╪º ┘è┘Å┘é╪º┘ê┘à.'],
    ['line1' => '╪¿╪┤╪▒╪¬┘â┘É ╪¬╪│╪¬╪¡┘é ╪º┘ä╪ú┘ü╪╢┘ä', 'line2' => '╪º╪«╪¬╪º╪▒┘è ┘à┘å ╪¼┘å┘è┘å.'],
    ['line1' => '╪ú╪¡╪»╪½ ╪º┘ä┘à╪º╪▒┘â╪º╪¬ ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐', 'line2' => '╪¿┘è┘å ┘è╪»┘è┘â┘É ╪º┘ä╪ó┘å.'],
    ['line1' => '╪╡╪º┘ä┘ê┘å┘â┘É ╪º┘ä┘à╪½╪º┘ä┘è', 'line2' => '┘ü╪«╪º┘à╪⌐ ┘à╪¬┘å╪º┘ç┘è╪⌐.'],
    ['line1' => '╪¬┘é┘å┘è╪º╪¬ ┘à╪¬╪╖┘ê╪▒╪⌐', 'line2' => '┘å╪¬╪º╪ª╪¼ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐.'],
    ['line1' => '╪¬╪¼┘ç┘è╪▓╪º╪¬ ┘à╪¬┘â╪º┘à┘ä╪⌐', 'line2' => '┘ä╪ú┘ü╪╢┘ä ╪╡╪º┘ä┘ê┘å.'],
    ['line1' => '╪¼┘ê╪»╪⌐ ╪╣╪º┘ä┘à┘è╪⌐', 'line2' => '╪«╪»┘à╪⌐ ┘à╪¡┘ä┘è╪⌐.'],
    ['line1' => '╪º┘â╪¬╪┤┘ü┘è ╪│╪▒ ╪º┘ä╪Ñ╪┤╪▒╪º┘é╪⌐', 'line2' => '┘à╪╣ ┘à┘å╪¬╪¼╪º╪¬ ╪¼┘å┘è┘å.'],
    ['line1' => '╪ú╪¼┘ç╪▓╪⌐ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐', 'line2' => '┘ä┘à╪│╪¬┘é╪¿┘ä ╪╡╪º┘ä┘ê┘å┘â┘É.'],
    ['line1' => '┘à╪│╪¬╪¡╪╢╪▒╪º╪¬ ╪╖╪¿┘è╪╣┘è╪⌐', 'line2' => '╪¿┘å╪¬╪º╪ª╪¼ ┘à╪╢┘à┘ê┘å╪⌐.'],
    ['line1' => '╪¬╪ú┘ä┘é┘è ╪¿╪½┘é╪⌐', 'line2' => '┘â┘ä ┘è┘ê┘à ┘à╪╣ ╪¼┘å┘è┘å.'],
    ['line1' => '╪┤╪¡┘å ╪│╪▒┘è╪╣', 'line2' => '┘ä┘â┘ä ┘ü┘ä╪│╪╖┘è┘å.'],
    ['line1' => '╪»┘ü╪╣ ╪╣┘å╪» ╪º┘ä╪º╪│╪¬┘ä╪º┘à', 'line2' => '╪½┘é╪⌐ ┘ê╪ú┘à╪º┘å.'],
    ['line1' => '╪ú┘ü╪╢┘ä ╪º┘ä╪ú╪│╪╣╪º╪▒', 'line2' => '┘ä╪ú╪¼┘ê╪» ╪º┘ä┘à┘å╪¬╪¼╪º╪¬.'],
    ['line1' => '╪«╪¿╪▒╪º╪í ╪º┘ä╪¼┘à╪º┘ä', 'line2' => '┘è╪«╪¬╪º╪▒┘ê┘å ┘ä┘â┘É.'],
    ['line1' => '╪▒┘ê╪¬┘è┘å ╪º┘ä╪╣┘å╪º┘è╪⌐', 'line2' => '┘è╪¬╪¡┘ê┘ä ╪Ñ┘ä┘ë ╪│╪¡╪▒.'],
    ['line1' => '┘â┘ä ┘à┘å╪¬╪¼ ┘è╪▒┘ê┘è', 'line2' => '┘é╪╡╪⌐ ╪¬┘à┘è╪▓.'],
    ['line1' => '╪º╪│╪¬╪½┘à╪▒┘è ┘ü┘è ╪¼┘à╪º┘ä┘â┘É', 'line2' => '┘ê╪º╪¼┘å┘è ╪º┘ä╪½┘å╪º╪í.'],
    ['line1' => '┘ä┘à╪│╪⌐ ┘ê╪º╪¡╪»╪⌐ ╪¬┘â┘ü┘è', 'line2' => '┘ä╪¬╪║┘è┘è╪▒ ┘â┘ä ╪┤┘è╪í.'],
    ['line1' => '╪¼┘å┘è┘å... ┘ê╪¼┘ç╪¬┘â┘É', 'line2' => '┘ä╪╣╪º┘ä┘à ╪º┘ä╪¼┘à╪º┘ä.'],
    ['line1' => '╪Ñ╪╖┘ä╪º┘ä╪⌐ ╪º┘ä┘å╪¼┘à╪º╪¬', 'line2' => '┘ü┘è ┘à┘å╪▓┘ä┘â┘É.'],
    ['line1' => '┘å╪¿┘å┘è ┘ä┘â┘É ┘é╪╡╪⌐ ┘å╪¼╪º╪¡', 'line2' => '┘à┘å ╪º┘ä╪╡┘ü╪▒ ╪Ñ┘ä┘ë ╪º┘ä┘é┘à╪⌐.'],
    ['line1' => '┘à┘å╪¬╪¼╪º╪¬ ╪º╪«╪¬╪▒┘å╪º┘ç╪º', 'line2' => '╪¿┘â┘ä ╪¡╪¿ ┘ê╪»┘é╪⌐.'],
    ['line1' => '┘å╪╣┘è╪» ╪¬╪╣╪▒┘è┘ü ╪º┘ä╪¼┘à╪º┘ä', 'line2' => '┘ü┘è ┘ü┘ä╪│╪╖┘è┘å.'],
    // ΓòÉΓòÉΓòÉΓòÉΓòÉ NEW HIGH-IMPACT HEADLINES ΓòÉΓòÉΓòÉΓòÉΓòÉ
    ['line1' => '┘à╪º╪▒┘â╪º╪¬ ╪╣╪º┘ä┘à┘è╪⌐ ╪ú╪╡┘ä┘è╪⌐', 'line2' => '╪¿╪ú╪│╪╣╪º╪▒ ╪¬┘å╪º┘ü╪│┘è╪⌐.'],
    ['line1' => '╪¬┘ê╪╡┘è┘ä ╪«┘ä╪º┘ä 24 ╪│╪º╪╣╪⌐', 'line2' => '┘ä┘â┘ä ┘à╪»┘å ┘ü┘ä╪│╪╖┘è┘å.'],
    ['line1' => '╪»╪╣┘à ┘ü┘å┘è ╪╣┘ä┘ë ┘à╪»╪º╪▒ ╪º┘ä┘è┘ê┘à', 'line2' => '┘å╪¡┘å┘Å ┘ç┘å╪º ┘ä┘à╪│╪º╪╣╪»╪¬┘â┘É.'],
    ['line1' => '╪ú┘â╪½╪▒ ┘à┘å 800 ┘à┘å╪¬╪¼', 'line2' => '┘ü┘è ┘à╪¬╪¼╪▒┘â┘É ╪º┘ä┘à┘ü╪╢┘ä.'],
    ['line1' => '╪½┘é╪⌐ 15,000 ╪╣┘à┘è┘ä╪⌐', 'line2' => '┘ä╪º ╪¬╪«╪╖╪ª┘è ╪º┘ä╪º╪«╪¬┘è╪º╪▒.'],
    ['line1' => '┘à┘å╪¬╪¼╪º╪¬ ┘à╪«╪¬╪º╪▒╪⌐ ╪¿╪╣┘å╪º┘è╪⌐', 'line2' => '┘ä╪ú┘å┘â┘É ╪¬╪│╪¬╪¡┘é┘è┘å.'],
    ['line1' => '╪ú╪¼┘ç╪▓╪⌐ ╪╡╪º┘ä┘ê┘å╪º╪¬ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐', 'line2' => '┘ä┘ä┘å╪¼╪º╪¡ ┘ê╪º┘ä╪¬┘à┘è╪▓.'],
    ['line1' => '╪¬╪¼┘à┘è┘ä ╪º╪¡╪¬╪▒╪º┘ü┘è', 'line2' => '┘è╪¿╪»╪ú ┘à┘å ╪º╪«╪¬┘è╪º╪▒┘â┘É.'],
    ['line1' => '┘ä┘à╪│╪⌐ ╪¼┘å┘è┘å', 'line2' => '╪¬┘ü╪▒┘é ┘à╪╣┘â┘É ╪»╪º╪ª┘à╪º┘ï.'],
    ['line1' => '╪¼┘ê╪»╪⌐ ┘ä╪º ╪¬┘Å╪╢╪º┘ç┘ë', 'line2' => '┘ê╪ú╪╡╪º┘ä╪⌐ ╪¬╪»┘ê┘à.'],
    ['line1' => '╪ú┘å┘é┘è ╪º┘ä┘à┘å╪¬╪¼╪º╪¬', 'line2' => '┘à┘å ┘à╪╡╪º╪»╪▒ ┘à┘ê╪½┘ê┘é╪⌐.'],
    ['line1' => '╪¬╪│┘ê┘é┘è ╪¿╪░┘â╪º╪í', 'line2' => '┘ê╪¬╪ú┘ä┘é┘è ╪¿╪½┘é╪⌐.'],
    ['line1' => '┘â┘ä ┘à╪º ╪¬╪¡╪¬╪º╪¼┘è┘å┘ç', 'line2' => '┘ä┘ä╪╣┘å╪º┘è╪⌐ ┘ê╪º┘ä╪¼┘à╪º┘ä.'],
    ['line1' => '╪╡╪º┘ä┘ê┘å┘â┘É ┘è╪│╪¬╪¡┘é ╪º┘ä╪ú┘ü╪╢┘ä', 'line2' => '┘ê┘å╪¡┘å┘Å ┘å┘é╪»┘à┘ç ┘ä┘â┘É.'],
    ['line1' => '╪¬╪¼╪▒╪¿╪⌐ ╪¬╪│┘ê┘é ┘ü╪▒┘è╪»╪⌐', 'line2' => '┘à╪╣ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä.'],
    ['line1' => '╪½┘é╪⌐ ┘ê╪¼┘ê╪»╪⌐', 'line2' => '┘ü┘è ┘â┘ä ╪╖┘ä╪¿.'],
    ['line1' => '╪¼┘à╪º┘ä┘â┘É.. ┘à╪│╪ñ┘ê┘ä┘è╪¬┘å╪º', 'line2' => '┘ê┘å╪¡┘å┘Å ┘å╪¡╪¿ ╪░┘ä┘â.'],
];

// Product slides with matching titles
$slidesData = [];
$slideProductIds = [];
$catIds = $categories->filter(fn($c) => $c->products_count > 0)->shuffle()->take(8);
foreach ($catIds as $cat) {
    $p = \App\Models\Product::where('category_id', $cat->id)->where('status', 'active')->inRandomOrder()->first();
    if (!$p) continue;
    $slideProductIds[$cat->id] = $p->id;
    $catName = $cat->display_name ?? $cat->name_ar;
    // ╪º╪│╪¬╪«╪»╪º┘à ┘à╪╡╪╖┘ä╪¡╪º╪¬ ╪ó┘à┘å╪⌐ ┘ä╪¬╪¼╪º┘ê╪▓ ┘ü┘ä╪º╪¬╪▒ ╪º┘ä┘à┘å╪╡╪º╪¬ ╪º┘ä╪Ñ╪╣┘ä╪º┘å┘è╪⌐ - ╪¬╪¼┘å╪¿ ┘â┘ä┘à╪⌐ "┘ä┘è╪▓╪▒"
    $safeDeviceTerms = ['╪¼┘ç╪º╪▓', '╪ú╪¼┘ç╪▓╪⌐', '╪¬┘é┘å┘è╪⌐', '╪¬┘â┘å┘ê┘ä┘ê╪¼┘è╪º', '┘å╪¿╪╢', '╪╢┘ê╪ª┘è', '┘à╪¬┘é╪»┘à', ' advanced', 'device', 'technology'];
    $isDevices = false;
    foreach ($safeDeviceTerms as $term) {
        if (str_contains($catName, $term)) { $isDevices = true; break; }
    }
    $isSalon = str_contains($catName, '╪╡╪º┘ä┘ê┘å') || str_contains($catName, '╪¬╪¼┘ç┘è╪▓');
    $slidesData[] = [
        'product' => $p,
        'category' => $cat,
        'title_line1' => $isDevices ? '╪¬┘é┘å┘è╪º╪¬ ┘à╪¬╪╖┘ê╪▒╪⌐' : ($isSalon ? '╪╡╪º┘ä┘ê┘å┘â ╪º┘ä┘à╪½╪º┘ä┘è' : '┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐'),
        'title_line2' => $isDevices ? '┘å╪¬╪º╪ª╪¼ ╪º╪¡╪¬╪▒╪º┘ü┘è╪⌐.' : ($isSalon ? '┘ü╪«╪º┘à╪⌐ ┘à╪¬┘å╪º┘ç┘è╪⌐.' : '╪¼┘à╪º┘ä ┘ä╪º ┘è┘Å┘é╪º┘ê┘à.'),
        'color' => $isDevices ? '#06b6d4' : ($isSalon ? '#d4af37' : '#ec4899'),
    ];
}
if (empty($slidesData) && $featuredProducts->isNotEmpty()) {
    $slidesData[] = [
        'product' => $featuredProducts->first(),
        'category' => null,
        'title_line1' => '┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐',
        'title_line2' => '┘å╪¬╪º╪ª╪¼ ┘à╪¿┘ç╪▒╪⌐.',
        'color' => '#ec4899',
    ];
}
// Pre-fetch sub-products in one batch to avoid N+1
$subProductsCache = [];
if (!empty($slideProductIds)) {
    $allSubProducts = \App\Models\Product::whereIn('category_id', array_keys($slideProductIds))
        ->where('status', 'active')
        ->whereNotIn('id', array_values($slideProductIds))
        ->inRandomOrder()
        ->get()
        ->groupBy('category_id');
    foreach ($allSubProducts as $cid => $prods) {
        $subProductsCache[$cid] = $prods->take(2);
    }
}
@endphp

<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop"
             class="w-full h-full object-cover opacity-10 mix-blend-luminosity"
             alt="" aria-hidden="true" loading="eager" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-b from-[#1a0533] via-[#2d0a5c]/95 to-[#0f172a]/90"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 pt-4 md:pt-8 pb-20 md:pb-28">
        <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12">

            <div class="w-full lg:w-[45%] text-center">

                {{-- Logo ΓÇö clean, no border --}}
                <div class="mb-5 flex justify-center">
                    @if(!empty($siteSettings['site_logo_url']))
                    <img src="{{ $siteSettings['site_logo_url'] }}" alt="╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä" class="h-8 sm:h-12 md:h-20 lg:h-28 w-auto object-contain drop-shadow-lg" style="max-height:112px;max-width:280px;">
                    @else
                    <span class="text-xl sm:text-2xl md:text-3xl tracking-wider text-white font-black" style="letter-spacing:0.12em;">╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä</span>
                    @endif
                </div>

                {{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
                     PREMIUM HERO CARD ΓÇö Animated gradient + floating elements
                     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
                <div id="heroCard" class="relative mb-6 select-none">
                    <div class="relative overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] p-6 sm:p-8 md:p-10" style="background:linear-gradient(145deg,rgba(60,20,60,0.55) 0%,rgba(40,15,45,0.45) 40%,rgba(var(--brand-500-rgb,255,42,133),0.18) 100%);border:1.5px solid rgba(236,72,153,0.25);backdrop-filter:blur(24px);box-shadow:0 24px 80px rgba(0,0,0,0.4),inset 0 1px 0 rgba(255,255,255,0.08),0 0 120px rgba(var(--brand-500-rgb,255,42,133),0.15);">

                        {{-- Ambient glow orbs --}}
                        <div class="absolute -top-28 -right-28 w-56 h-56 rounded-full opacity-20 pointer-events-none" style="background:radial-gradient(circle,var(--brand-500),transparent 70%);filter:blur(60px);animation:glowPulse 5s ease-in-out infinite;"></div>
                        <div class="absolute -bottom-28 -left-28 w-56 h-56 rounded-full opacity-12 pointer-events-none" style="background:radial-gradient(circle,#06b6d4,transparent 70%);filter:blur(60px);animation:glowPulse 6s ease-in-out infinite 1s;"></div>

                        {{-- Animated border shimmer --}}
                        <div class="absolute inset-0 rounded-[2rem] sm:rounded-[2.5rem] pointer-events-none" style="background:linear-gradient(135deg,transparent 40%,rgba(255,255,255,0.04) 50%,transparent 60%);background-size:200% 200%;animation:borderShimmer 5s ease-in-out infinite;"></div>

                        {{-- Floating decorative elements --}}
                        <div class="absolute top-4 right-6 text-white/10 pointer-events-none animate-bounce" style="animation-duration:3s;"><i class="ph-fill ph-sparkle text-xl"></i></div>
                        <div class="absolute bottom-8 left-6 text-white/10 pointer-events-none animate-bounce" style="animation-duration:4s;animation-delay:1s;"><i class="ph-fill ph-star text-lg"></i></div>
                        <div class="absolute top-1/2 right-3 text-white/5 pointer-events-none animate-pulse"><i class="ph-fill ph-diamond text-xs"></i></div>

                        {{-- Premium badge --}}
                        <div class="flex justify-center mb-5 relative z-10">
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black tracking-[0.15em] uppercase" style="background:rgba(var(--brand-500-rgb,255,42,133),0.12);color:var(--brand-500);border:1px solid rgba(var(--brand-500-rgb,255,42,133),0.2);box-shadow:0 0 30px rgba(var(--brand-500-rgb,255,42,133),0.1),inset 0 1px 0 rgba(255,255,255,0.1);">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-60"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                                </span>
                                ╪╣╪▒┘ê╪╢ ╪¡╪╡╪▒┘è╪⌐
                            </span>
                        </div>

                        {{-- Rotating Headline with scale animation --}}
                        <div class="relative overflow-hidden mb-3" style="height:100px;">
                            @foreach($heroHeadlines as $i => $headline)
                            <div class="hero-headline absolute w-full text-center px-2" style="top:0;left:0;opacity:{{ $i === 0 ? '1' : '0' }};transform:translateY({{ $i === 0 ? '0' : '20px' }}) scale({{ $i === 0 ? '1' : '0.95' }});transition:opacity 0.7s cubic-bezier(0.4,0,0.2,1),transform 0.7s cubic-bezier(0.4,0,0.2,1);pointer-events:{{ $i === 0 ? 'auto' : 'none' }};" data-headline="{{ $i }}">
                                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-[2.75rem] font-black leading-[1.12] tracking-tight">
                                    <span class="block hero-line-1" style="background:linear-gradient(135deg,#fff 30%,#f0abfc 70%,#fff 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;background-size:200% auto;animation:shineText 4s linear infinite;">{{ $headline['line1'] }}</span>
                                    <span class="block mt-1.5" style="color:rgba(255,255,255,0.88);text-shadow:0 0 25px rgba(255,255,255,0.15),0 3px 8px rgba(0,0,0,0.3);">{{ $headline['line2'] }}</span>
                                </h1>
                            </div>
                            @endforeach
                        </div>

                        {{-- Elegant divider --}}
                        <div class="flex items-center gap-3 justify-center mb-4 relative z-10">
                            <div class="h-px flex-1 max-w-[50px]" style="background:linear-gradient(to left,transparent,rgba(255,255,255,0.25));"></div>
                            <div class="w-7 h-7 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);">
                                <i class="ph-fill ph-sparkle text-brand-500/70 text-xs"></i>
                            </div>
                            <div class="h-px flex-1 max-w-[50px]" style="background:linear-gradient(to right,transparent,rgba(255,255,255,0.25));"></div>
                        </div>

                        {{-- Rotating Marketing Phrase --}}
                        <div class="relative overflow-hidden" style="height:50px;">
                            @foreach($allPhrases as $i => $phrase)
                            <p class="hero-phrase absolute w-full text-center text-sm sm:text-base md:text-lg font-semibold leading-relaxed px-4"
                               style="top:0;left:0;color:rgba(255,255,255,0.6);opacity:{{ $i === 0 ? '1' : '0' }};transform:translateY({{ $i === 0 ? '0' : '10px' }});transition:opacity 0.6s ease,transform 0.6s ease;pointer-events:{{ $i === 0 ? 'auto' : 'none' }};"
                               data-phrase="{{ $i }}">{{ $phrase }}</p>
                            @endforeach
                        </div>

                        {{-- Progress dots with count --}}
                        <div class="flex items-center justify-center gap-2 mt-5 relative z-10">
                            <span class="text-[10px] font-bold text-white/30" id="heroCounter">1 / {{ count($heroHeadlines) }}</span>
                            <div class="flex gap-1.5">
                                @foreach($heroHeadlines as $i => $h)
                                <span class="hero-dot block h-1 rounded-full transition-all duration-500 {{ $i === 0 ? 'bg-gradient-to-r from-brand-400 to-brand-600 w-5' : 'bg-white/15 w-1' }}"></span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CTA Buttons ΓÇö ┘à╪▒┘å╪⌐ ┘ê╪║┘è╪▒ ╪Ñ┘ä╪▓╪º┘à┘è╪⌐ ┘ä╪¬╪¼┘å╪¿ Engagement-Bait ╪╣┘ä┘ë ╪º┘ä┘à┘å╪╡╪º╪¬ ╪º┘ä╪Ñ╪╣┘ä╪º┘å┘è╪⌐ --}}
                <div class="flex flex-col items-center gap-3 mb-8">
                    <a href="{{ route('shop') }}" class="w-full sm:w-72 px-8 py-4 rounded-full font-black text-sm tracking-wide inline-flex items-center justify-center gap-2 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-0.5" style="background:#ffffff;color:#0f172a;">
                        ╪º┘â╪¬╪┤┘ü┘è ╪º┘ä┘à┘å╪¬╪¼╪º╪¬ <i class="fa-solid fa-arrow-left mr-1"></i>
                    </a>
                    <a href="{{ route('shop') }}" class="text-white/60 hover:text-white transition-colors font-medium text-sm">
                        ╪¬╪╡┘ü╪¡┘è ╪º┘ä┘à╪¬╪¼╪▒ ΓÇö ╪º┘ä┘à╪¬╪º╪¿╪╣╪⌐ ╪º╪«╪¬┘è╪º╪▒┘è╪⌐
                    </a>
                </div>
            </div>

            {{-- Product Slides --}}
            <div class="w-full lg:w-[55%] relative flex justify-center">
                <div class="relative w-full max-w-lg">
                    @foreach($slidesData as $index => $slide)
                    @php $main = $slide['product']; $cat = $slide['category']; @endphp
                    <div class="hero-slide rounded-3xl overflow-hidden p-3 {{ $index === 0 ? '' : 'hidden' }}" data-slide="{{ $index }}" style="background:rgba(255,255,255,0.08);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.15);">
                        <a href="{{ route('product.show', $main->slug) }}" class="block relative rounded-2xl overflow-hidden bg-surface-alt group" style="height:280px;">
                            @if($main->main_image_url)
                            <img src="{{ $main->optimizedImageUrl(800) }}" alt="{{ $main->name_ar }}" width="800" height="380"
                                 class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-700" loading="{{ $index === 0 ? 'eager' : 'lazy' }}"{{ $index === 0 ? ' fetchpriority="high"' : '' }}>
                            @else
                            <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-flask text-5xl text-ink-dim/15"></i></div>
@endif
                            <div class="absolute bottom-0 left-0 right-0 p-5 bg-gradient-to-t from-surface/95 via-surface/70 to-transparent">
                                @if($cat)<span class="inline-block px-2.5 py-1 rounded-full text-white text-[11px] font-bold mb-2" style="background:{{ $slide['color'] }};">{{ $cat->display_name ?? $cat->name_ar }}</span>@endif
                                <h3 class="text-lg font-black text-white mb-1">{{ $main->name_ar }}</h3>
                                <span class="font-black text-2xl md:text-3xl" style="color:#ffffff;text-shadow:0 0 12px rgba(255,255,255,0.3),0 0 2px rgba(255,255,255,0.5);">{{ number_format($main->final_b2c_price ?? $main->b2c_price, 0) }} Γé¬</span>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
     SECTION: Categories ΓÇö BringUs Style (Circular image, white card, pink border)
     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
@if($categories->isNotEmpty())
<section class="categories-section py-12 relative z-20" style="background:#fafafa;">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-black mb-2" style="color:#1a1a1a;">╪¬╪│┘ê┘é┘è ╪¡╪│╪¿ <span style="color:#ec4899;">╪º┘ä┘é╪│┘à</span></h2>
            <p class="max-w-2xl mx-auto text-sm md:text-base" style="color:#888;">╪º┘â╪¬╪┤┘ü┘è ┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ ┘à┘å ╪ú┘ü╪╢┘ä ╪º┘ä┘à╪º╪▒┘â╪º╪¬ ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐ ┘ü┘è ╪¼┘à┘è╪╣ ╪ú┘é╪│╪º┘à ╪º┘ä╪¬╪¼┘à┘è┘ä ┘ê╪º┘ä╪╣┘å╪º┘è╪⌐</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-4">
            @foreach($categories as $cat)
            @php
                $arName = $cat->display_name ?? $cat->name_ar;
                $enName = $cat->name_en ?? '';
            @endphp
            <a href="{{ route('shop', ['category' => $cat->slug]) }}"
               class="group flex flex-col items-center text-center rounded-2xl p-3 md:p-4 transition-all duration-300 hover:shadow-md"
               style="background:#fff;border:1.5px solid #E8D5E0;">
                {{-- Circular image --}}
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full overflow-hidden flex-shrink-0 mb-3" style="border:2px solid #f3e8f3;background:#fdf2f8;">
                    @if($cat->image)
                    <img src="{{ asset($cat->image) }}" alt="{{ $arName }}"
                         class="w-full h-full object-cover" loading="lazy">
                    @else
                    <div class="w-full h-full flex items-center justify-center" style="background:#fce7f3;">
                        <i class="fa-solid fa-tag text-lg" style="color:#ec4899;"></i>
                    </div>
                    @endif
                </div>
                {{-- Arabic name --}}
                <h3 class="font-bold text-xs md:text-sm mb-0.5 leading-tight" style="color:#1a1a1a;">
                    {{ $arName }}
                </h3>
                {{-- English name --}}
                @if($enName)
                <span class="text-[10px] md:text-[11px]" style="color:#999;">{{ $enName }}</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- WhatsApp FAB --}}
@if(!empty($siteSettings['whatsapp_number']))
<a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$siteSettings['whatsapp_number']) }}" target="_blank" rel="noopener"
   class="fixed z-[999] flex items-center justify-center shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 hover:scale-105"
   style="bottom:24px;right:20px;width:56px;height:56px;background:#25D366;border-radius:50%;" aria-label="┘ê╪º╪¬╪│╪º╪¿">
    <i class="ph-fill ph-whatsapp-logo text-white text-2xl"></i>
</a>
@endif

{{-- Hide theme switcher on mobile; keep floating social visible --}}
@push('styles')
<style>
    @media (max-width:1024px) {
        #themeSwitcher { display: none !important; }
        .floating-social-v3 { opacity: 0.7; }
    }
    @media (min-width:1025px) {
        .floating-social-v3 { opacity: 0.4; transition: opacity 0.3s; }
        .floating-social-v3:hover { opacity: 1; }
        #themeSwitcher { opacity: 0.4; transition: opacity 0.3s; }
        #themeSwitcher:hover { opacity: 1; }
    }
</style>
@endpush

<script>
(function() {
    // Unified rotator: headline + phrase + dot change together
    var headlines = document.querySelectorAll('.hero-headline');
    var phrases = document.querySelectorAll('.hero-phrase');
    var heroDots = document.querySelectorAll('.hero-dot');
    var totalH = headlines.length;
    var totalP = phrases.length;
    var currentIdx = 0, interval;

    function showSlide(idx) {
        // Headlines with scale
        headlines.forEach(function(h, i) {
            var active = i === idx;
            h.style.opacity = active ? '1' : '0';
            h.style.transform = active ? 'translateY(0) scale(1)' : 'translateY(20px) scale(0.95)';
            h.style.pointerEvents = active ? 'auto' : 'none';
        });
        // Phrases (cycle through independently mapped to headline index)
        var phraseIdx = idx % totalP;
        phrases.forEach(function(p, i) {
            p.style.opacity = i === phraseIdx ? '1' : '0';
            p.style.transform = i === phraseIdx ? 'translateY(0)' : 'translateY(10px)';
        });
        // Dots with gradient
        heroDots.forEach(function(d, i) {
            d.className = i === idx
                ? 'hero-dot block h-1 rounded-full bg-gradient-to-r from-brand-400 to-brand-600 w-5 transition-all duration-500'
                : 'hero-dot block h-1 rounded-full bg-white/15 w-1 transition-all duration-500';
        });
        // Counter
        var counter = document.getElementById('heroCounter');
        if (counter) counter.textContent = (idx + 1) + ' / ' + totalH;
        currentIdx = idx;
    }

    function next() { showSlide((currentIdx + 1) % totalH); }
    interval = setInterval(next, 4000);

    // Pause on hover
    var heroCard = document.getElementById('heroCard');
    if (heroCard) {
        heroCard.addEventListener('mouseenter', function() { clearInterval(interval); });
        heroCard.addEventListener('mouseleave', function() { interval = setInterval(next, 4000); });
    }

    // Product slides rotator (independent)
    var slides = document.querySelectorAll('.hero-slide');
    var totalS = slides.length;
    var currentS = 0, sInterval;

    function showProductSlide(idx) {
        slides.forEach(function(s) { s.classList.add('hidden'); });
        var s = document.querySelector('.hero-slide[data-slide="' + idx + '"]');
        if (s) s.classList.remove('hidden');
        currentS = idx;
    }
    function nextProductSlide() { showProductSlide((currentS + 1) % totalS); }
    if (totalS > 1) sInterval = setInterval(nextProductSlide, 6000);

    slides.forEach(function(s) {
        s.addEventListener('mouseenter', function() { clearInterval(sInterval); });
        s.addEventListener('mouseleave', function() { if (totalS > 1) sInterval = setInterval(nextProductSlide, 6000); });
    });
})();
</script>

<style>
    @keyframes borderShimmer {
        0% { background-position: 200% 200%; }
        100% { background-position: -200% -200%; }
    }
    @keyframes glowPulse {
        0%, 100% { opacity: 0.15; transform: scale(1); }
        50% { opacity: 0.25; transform: scale(1.1); }
    }
    @keyframes shineText {
        0% { background-position: 200% center; }
        100% { background-position: -200% center; }
    }
    .value-card:hover { transform: translateY(-6px); border-color: rgba(255,42,133,0.15); box-shadow: 0 12px 40px rgba(0,0,0,0.3), var(--neon-glow); }
    @media (max-width: 767px) {
        .home-sections { display: flex; flex-direction: column; }
    }
    @media (min-width: 768px) {
        .home-sections { display: flex; flex-direction: column; }
        .home-sections .products-section { order: 2; }
        .home-sections .categories-section { order: 1; }
    }
</style>

<div class="home-sections">

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
     SECTION: Products FIRST on mobile
     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
<section id="products" class="products-section py-20 relative">

    {{-- Mobile: visual separator --}}
    <div class="md:hidden -mt-20 mb-8 text-center">
        <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full font-black text-sm shadow-lg" style="background:var(--gradient-primary);color:#fff;">
            <i class="fa-solid fa-star text-xs"></i> ┘à┘å╪¬╪¼╪º╪¬┘å╪º ╪º┘ä┘à┘à┘è╪▓╪⌐ <i class="fa-solid fa-star text-xs"></i>
        </div>
    </div>

    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_50%,rgba(var(--brand-500-rgb,255,42,133),0.04),transparent_60%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="mb-16 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">┘à╪«╪¬╪¿╪▒ ╪º┘ä╪¼┘à╪º┘ä</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-4">┘à┘å╪¬╪¼╪º╪¬ ┘à╪«╪¬╪º╪▒╪⌐ <span class="gradient-text bg-[length:200%_auto]">╪¿╪╣┘å╪º┘è╪⌐ ┘ü╪º╪ª┘é╪⌐</span></h2>
            <p class="text-ink-dim max-w-2xl mx-auto text-lg font-light">┘â┘ä ┘à┘å╪¬╪¼ ┘ü┘è ┘à╪¬╪¼╪▒┘å╪º ╪¬┘à ╪º┘å╪¬┘é╪º╪ñ┘ç ╪¿╪╣┘å╪º┘è╪⌐ ┘à┘å ╪ú┘ü╪╢┘ä ╪º┘ä┘à╪º╪▒┘â╪º╪¬ ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐ ┘ä┘è┘â┘ê┘å ╪¼╪▓╪í╪º┘ï ┘à┘å ╪▒┘ê╪¬┘è┘å ╪╣┘å╪º┘è╪¬┘â ╪º┘ä╪┤╪«╪╡┘è. ┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐╪î ┘å╪¬╪º╪ª╪¼ ┘à╪╢┘à┘ê┘å╪⌐.</p>
        </div>

        @if($featuredProducts->isNotEmpty() || $newProducts->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8">

            @php $bigProduct = $featuredProducts->first(); @endphp
            @if($bigProduct)
            {{-- Large Featured Product Card (col-span-8) --}}
            <div class="md:col-span-7 lg:col-span-8 group relative rounded-[2rem] overflow-hidden glass-panel border border-white/5 h-[450px] cursor-pointer"
                 onclick="window.location='{{ route('product.show', $bigProduct->slug) }}'">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent z-10"></div>
                @if($bigProduct->main_image_url)
                <img src="{{ $bigProduct->optimizedImageUrl(800, 450) }}" alt="{{ $bigProduct->name_ar }}" width="800" height="450"
                     class="absolute inset-0 w-full h-full object-cover filter grayscale mix-blend-luminosity group-hover:scale-105 transition-transform duration-700"
                     loading="lazy">
                @else
                <div class="absolute inset-0 flex items-center justify-center text-white/10"><i class="fa-solid fa-flask text-8xl"></i></div>
                @endif

                <div class="absolute top-6 right-6 z-20 flex gap-2">
                    <span class="bg-black/50 backdrop-blur px-3 py-1 rounded-full text-xs border border-white/10 text-ink/70">┘à┘å╪¬╪¼╪º╪¬ ┘à┘à┘è╪▓╪⌐</span>
                    <span class="pill-brand backdrop-blur text-xs">╪º┘ä╪ú┘â╪½╪▒ ┘à╪¿┘è╪╣╪º┘ï</span>
                </div>

                <div class="absolute bottom-8 right-8 z-20 text-right">
                    <h3 class="text-3xl font-black mb-2 text-white">{{ $bigProduct->name_ar }}</h3>
                    @if($bigProduct->brand)
                    <p class="text-ink-dim text-sm mb-3">{{ $bigProduct->brand->name }}</p>
                    @endif
                    <div class="flex items-center justify-end gap-4">
                        <span class="text-2xl font-bold text-brand-500">{{ number_format($bigProduct->b2c_price, 0) }} Γé¬</span>
                        <button onclick="event.stopPropagation(); addToCart({{ $bigProduct->id }})"
                                class="w-12 h-12 rounded-full bg-white style="color:#0f172a;" flex items-center justify-center hover:shadow-neon transition-all"
                                aria-label="╪Ñ╪╢╪º┘ü╪⌐ ┘ä┘ä╪│┘ä╪⌐">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @php $secondProduct = $newProducts->first() ?? $featuredProducts->skip(1)->first(); @endphp
            @if($secondProduct)
            <div class="md:col-span-5 lg:col-span-4 group relative rounded-[2rem] overflow-hidden glass-panel border border-white/5 h-[450px] cursor-pointer"
                 onclick="window.location='{{ route('product.show', $secondProduct->slug) }}'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent z-10"></div>
                <div class="absolute inset-0 bg-accent-500/5 mix-blend-overlay z-10 group-hover:bg-accent-500/10 transition-colors"></div>
                @if($secondProduct->main_image_url)
                <img src="{{ $secondProduct->optimizedImageUrl(600, 450) }}" alt="{{ $secondProduct->name_ar }}" width="600" height="450"
                     class="absolute inset-0 w-full h-full object-cover filter contrast-125 group-hover:scale-105 transition-transform duration-700"
                     loading="lazy">
                @else
                <div class="absolute inset-0 flex items-center justify-center text-white/10"><i class="fa-solid fa-droplet text-8xl"></i></div>
                @endif

                <div class="absolute top-6 right-6 z-20">
                    <span class="pill-accent backdrop-blur text-xs">┘ê╪╡┘ä ╪¡╪»┘è╪½╪º┘ï</span>
                </div>

                <div class="absolute bottom-8 right-8 z-20 text-right">
                    <h3 class="text-xl font-black mb-1 text-white">{{ $secondProduct->name_ar }}</h3>
                    @if($secondProduct->brand)
                    <p class="text-ink-dim text-xs mb-4">{{ $secondProduct->brand->name }}</p>
                    @endif
                    <div class="flex items-center justify-between">
                        <button onclick="event.stopPropagation(); addToCart({{ $secondProduct->id }})"
                                class="text-xs font-bold uppercase tracking-wider border-b border-white/30 hover:text-brand-500 hover:border-brand-500 transition-colors pb-1 text-white/70">
                            ╪¬┘ü╪º╪╡┘è┘ä
                        </button>
                        <span class="font-bold text-white">{{ number_format($secondProduct->b2c_price, 0) }} Γé¬</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Info/Value Card (col-span-5) --}}
            <div class="md:col-span-5 rounded-[2rem] glass-panel border border-white/5 p-10 flex flex-col justify-between relative overflow-hidden group cursor-default">
                <div class="absolute -left-20 -top-20 w-64 h-64 bg-brand-500/8 rounded-full blur-3xl group-hover:bg-brand-500/12 transition-colors"></div>
                <div class="absolute top-0 right-8 left-8 h-[2px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: var(--gradient-primary);"></div>
                <div class="relative z-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-accent-500/10 flex items-center justify-center mb-6 shadow-accent-neon mx-auto">
                        <i class="fa-solid fa-microchip text-2xl text-accent-500"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4" style="color: var(--ink);">╪▒┘ê╪¬┘è┘å ╪╣┘å╪º┘è╪⌐<br>┘à╪╡┘à┘à ╪«╪╡┘è╪╡╪º┘ï ┘ä┘â┘É.</h3>
                    <p class="text-ink-dim text-sm leading-relaxed">
                        ┘å╪«╪¬╪º╪▒ ┘ä┘â┘É ╪ú┘ü╪╢┘ä ╪º┘ä┘à┘å╪¬╪¼╪º╪¬ ╪º┘ä┘à┘å╪º╪│╪¿╪⌐ ┘ä┘å┘ê╪╣ ╪¿╪┤╪▒╪¬┘â ┘ê╪º╪¡╪¬┘è╪º╪¼╪º╪¬┘â. ╪¬╪╡┘ü╪¡┘è ┘à╪¼┘à┘ê╪╣╪¬┘å╪º ╪º┘ä┘à┘à┘è╪▓╪⌐ ┘à┘å ┘à┘å╪¬╪¼╪º╪¬ ╪º┘ä╪╣┘å╪º┘è╪⌐ ╪¿╪º┘ä╪¿╪┤╪▒╪⌐ ┘ê╪º┘ä╪┤╪╣╪▒╪î ┘ê╪¬┘à╪¬╪╣┘è ╪¿╪¬╪¼╪▒╪¿╪⌐ ╪¬╪│┘ê┘é ┘ü╪▒┘è╪»╪⌐ ┘à╪╣ ╪┤╪¡┘å ╪│╪▒┘è╪╣ ┘ê╪»┘ü╪╣ ╪ó┘à┘å.
                    </p>
                </div>
                <div class="mt-8 text-center">
                    <a href="{{ route('shop') }}" class="text-accent-500 font-bold flex items-center justify-center gap-2 hover:gap-4 transition-all group/link">
                        ╪¬╪╡┘ü╪¡┘è ╪º┘ä┘à╪¬╪¼╪▒ <i class="fa-solid fa-arrow-left text-sm group-hover/link:-translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            @php $thirdProduct = $featuredProducts->skip(1)->first() ?? $newProducts->skip(1)->first() ?? $featuredProducts->skip(2)->first(); @endphp
            @if($thirdProduct)
            <div class="md:col-span-7 group relative rounded-[2rem] overflow-hidden glass-panel border border-white/5 h-[300px] cursor-pointer"
                 onclick="window.location='{{ route('product.show', $thirdProduct->slug) }}'">
                <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
                @if($thirdProduct->main_image_url)
                <img src="{{ $thirdProduct->optimizedImageUrl(600, 300) }}" alt="{{ $thirdProduct->name_ar }}" width="600" height="300"
                     class="absolute inset-0 w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition-all duration-1000"
                     loading="lazy">
                @else
                <div class="absolute inset-0 flex items-center justify-center text-white/10"><i class="fa-solid fa-box-open text-8xl"></i></div>
                @endif

                <div class="absolute top-1/2 transform -translate-y-1/2 right-12 z-20 text-right max-w-sm">
                    <h3 class="text-2xl font-black mb-2 text-white">{{ $thirdProduct->name_ar }}</h3>
                    @if($thirdProduct->brand)
                    <p class="text-ink-dim text-xs mb-5">{{ $thirdProduct->brand->name }}</p>
                    @endif
                    <button onclick="event.stopPropagation(); addToCart({{ $thirdProduct->id }})"
                            class="px-6 py-2.5 bg-white style="color:#0f172a;" rounded-full font-bold transition-all text-sm hover:shadow-neon hover:scale-105 inline-flex items-center gap-2">
                        <i class="fa-solid fa-plus text-xs"></i> ╪Ñ╪╢╪º┘ü╪⌐ ┘ä┘ä┘à╪«╪¬╪¿╪▒ ΓÇö {{ number_format($thirdProduct->b2c_price, 0) }} Γé¬
                    </button>
                </div>
            </div>
            @endif

        </div>
        @else
        <div class="text-center py-20 text-ink-dim">
            <i class="fa-solid fa-flask text-5xl mb-6 opacity-20"></i>
            <p class="text-lg">┘ä┘à ┘è╪¬┘à ╪Ñ╪╢╪º┘ü╪⌐ ┘à┘å╪¬╪¼╪º╪¬ ╪¿╪╣╪».</p>
        </div>
        @endif
    </div>
</section>

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
     SECTION 3: Trust Bar ΓÇö Social Proof & Quick Stats
     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
<section class="py-12 border-b border-ink/10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black gradient-text bg-[length:200%_auto] block mb-2">+{{ \App\Models\Product::count() }}</span>
                <span class="text-sm text-ink-muted">┘à┘å╪¬╪¼ ╪ú╪╡┘ä┘è</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-ink block mb-2">15,000+</span>
                <span class="text-sm text-ink-muted">╪╣┘à┘è┘ä╪⌐ ╪│╪╣┘è╪»╪⌐</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-ink block mb-2">4.9</span>
                <span class="text-sm text-ink-muted">╪¬┘é┘è┘è┘à ╪º┘ä╪╣┘à┘ä╪º╪í</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-ink block mb-2">24H</span>
                <span class="text-sm text-ink-muted">╪¬┘ê╪╡┘è┘ä ╪│╪▒┘è╪╣</span>
            </div>
        </div>
    </div>
</section>

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
     SECTION 3: Brand USP ΓÇö Optimized for Facebook & Google Ads
     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
<section class="py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_50%,rgba(var(--brand-500-rgb,255,42,133),0.04),transparent_60%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-5xl font-black mb-4">╪ú┘ü╪╢┘ä ┘ê╪¼┘ç╪¬┘â <span class="gradient-text bg-[length:200%_auto]">┘ä┘ä╪╣┘å╪º┘è╪⌐ ╪¿╪º┘ä╪¿╪┤╪▒╪⌐ ┘ê╪º┘ä╪┤╪╣╪▒</span></h2>
            <p class="text-ink-dim max-w-3xl mx-auto text-lg font-light leading-relaxed">┘à╪¬╪¼╪▒ ╪º┘ä┘â╪¬╪▒┘ê┘å┘è ┘à╪¬╪«╪╡╪╡ ┘ü┘è ┘à┘å╪¬╪¼╪º╪¬ ╪º┘ä╪¬╪¼┘à┘è┘ä ┘ê╪º┘ä╪╣┘å╪º┘è╪⌐ ╪¿╪º┘ä╪¿╪┤╪▒╪⌐╪î ┘å┘ê┘ü╪▒ ┘ä┘â┘É ┘à╪º╪▒┘â╪º╪¬ ╪╣╪º┘ä┘à┘è╪⌐ ╪ú╪╡┘ä┘è╪⌐ ╪¿╪ú╪│╪╣╪º╪▒ ╪¬┘å╪º┘ü╪│┘è╪⌐╪î ┘à╪╣ ╪┤╪¡┘å ╪│╪▒┘è╪╣ ┘ä╪¼┘à┘è╪╣ ┘à╪»┘å ┘ü┘ä╪│╪╖┘è┘å. ╪º┘â╪¬╪┤┘ü┘è ╪╣╪▒┘ê╪╢┘å╪º ╪º┘ä╪¡╪╡╪▒┘è╪⌐ ┘ê╪«╪»┘à╪⌐ ╪º┘ä╪╣┘à┘ä╪º╪í ╪º┘ä┘à┘à┘è╪▓╪⌐.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="glass-panel rounded-2xl p-7 text-center hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 mx-auto group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-certificate text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ ┘à╪╢┘à┘ê┘å╪⌐</h3>
                <p class="text-ink-dim text-sm leading-relaxed">╪¼┘à┘è╪╣ ┘à┘å╪¬╪¼╪º╪¬┘å╪º ╪ú╪╡┘ä┘è╪⌐ 100% ┘ê┘à╪│╪¬┘ê╪▒╪»╪⌐ ┘à┘å ┘à╪╡╪º╪»╪▒ ┘à┘ê╪½┘ê┘é╪⌐ ┘ê┘à╪╣╪¬┘à╪»╪⌐ ╪»┘ê┘ä┘è╪º┘ï. ┘å╪╢┘à┘å ┘ä┘â┘É ╪º┘ä╪¼┘ê╪»╪⌐ ┘ê╪º┘ä╪ú╪╡╪º┘ä╪⌐ ┘ü┘è ┘â┘ä ╪╖┘ä╪¿.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-center hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 mx-auto group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-truck-fast text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">╪¬┘ê╪╡┘è┘ä ┘ä┘â┘ä ┘ü┘ä╪│╪╖┘è┘å</h3>
                <p class="text-ink-dim text-sm leading-relaxed">┘å┘ê╪╡┘ä ╪╖┘ä╪¿┘â ┘ä╪¿╪º╪¿ ╪¿┘è╪¬┘â ┘ü┘è ╪º┘ä╪╢┘ü╪⌐ ╪º┘ä╪║╪▒╪¿┘è╪⌐╪î ╪º┘ä┘é╪»╪│╪î ┘ê╪º┘ä╪»╪º╪«┘ä ╪º┘ä┘à╪¡╪¬┘ä. ╪┤╪¡┘å ╪│╪▒┘è╪╣ ┘ê╪¬╪¬╪¿╪╣ ┘à╪¿╪º╪┤╪▒ ┘ä╪┤╪¡┘å╪¬┘â ╪¡╪¬┘ë ╪º┘ä╪º╪│╪¬┘ä╪º┘à.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-center hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 mx-auto group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-tags text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">╪ú┘ü╪╢┘ä ╪º┘ä╪ú╪│╪╣╪º╪▒ ┘ê╪º┘ä╪╣╪▒┘ê╪╢</h3>
                <p class="text-ink-dim text-sm leading-relaxed">╪ú╪│╪╣╪º╪▒ ╪¬┘å╪º┘ü╪│┘è╪⌐ ┘à╪╣ ╪╣╪▒┘ê╪╢ ╪¡╪╡╪▒┘è╪⌐ ┘ê╪«╪╡┘ê┘à╪º╪¬ ┘è┘ê┘à┘è╪⌐. ╪º┘ä╪»┘ü╪╣ ╪╣┘å╪» ╪º┘ä╪º╪│╪¬┘ä╪º┘à ┘à╪¬╪º╪¡ ┘ä╪▒╪º╪¡╪¬┘â ┘ê╪ú┘à╪º┘å┘â ╪º┘ä╪¬╪º┘à.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-center hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 mx-auto group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-headset text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">╪»╪╣┘à ╪º╪¡╪¬╪▒╪º┘ü┘è ┘à╪¬┘ê╪º╪╡┘ä</h3>
                <p class="text-ink-dim text-sm leading-relaxed">┘ü╪▒┘è┘é ╪«╪»┘à╪⌐ ╪╣┘à┘ä╪º╪í ┘à╪¡╪¬╪▒┘ü ╪¼╪º┘ç╪▓ ┘ä┘à╪│╪º╪╣╪»╪¬┘â ┘è┘ê┘à┘è╪º┘ï ┘à┘å 9 ╪╡╪¿╪º╪¡╪º┘ï ╪¡╪¬┘ë 10 ┘à╪│╪º╪í┘ï ╪╣╪¿╪▒ ╪º┘ä┘ê╪º╪¬╪│╪º╪¿. ╪º╪│╪¬┘ü╪│╪▒┘è ┘ê╪│┘å╪▒╪» ┘ü┘ê╪▒╪º┘ï.</p>
            </div>
        </div>
    </div>
</section>

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
      SECTION 4: Why ╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä? ΓÇö Premium Value Cards
      ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_50%,rgba(var(--brand-500-rgb,255,42,133),0.03),transparent_70%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="mb-16 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">┘ä┘à╪º╪░╪º ╪¬╪«╪¬╪º╪▒┘è┘å╪º</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-4">┘ä┘à╪º╪░╪º <span class="gradient-text bg-[length:200%_auto]">╪┤╪▒┘â╪⌐ ╪¼┘å┘è┘å ┘ä┘ä╪¬╪¼┘à┘è┘ä</span><span class="text-brand-500">.</span></h2>
            <p class="text-ink-dim max-w-2xl mx-auto text-lg font-light">┘à╪¬╪¼╪▒ ╪º┘ä╪╣┘å╪º┘è╪⌐ ╪¿╪º┘ä╪¿╪┤╪▒╪⌐ ╪º┘ä╪ú┘ê┘ä ┘ü┘è ┘ü┘ä╪│╪╖┘è┘å. ┘å┘ê┘ü╪▒ ┘ä┘â┘É ╪¬╪¼╪▒╪¿╪⌐ ╪¬╪│┘ê┘é ╪ó┘à┘å╪⌐ ┘ê┘à┘ê╪½┘ê┘é╪⌐ ┘à╪╣ ┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ ┘ê╪«╪»┘à╪⌐ ╪╣┘à┘ä╪º╪í ╪º╪│╪¬╪½┘å╪º╪ª┘è╪⌐.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            @php
                $valueCards = [
                    ['num' => '01', 'icon' => 'fa-solid fa-shield-check', 'title' => '┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ 100%', 'desc' => '┘å╪╢┘à┘å ┘ä┘â┘É ╪ú╪╡╪º┘ä╪⌐ ┘â┘ä ┘à┘å╪¬╪¼ ┘à┘å ┘à╪╡╪º╪»╪▒ ┘à┘ê╪½┘ê┘é╪⌐ ┘ê┘à╪╣╪¬┘à╪»╪⌐ ╪»┘ê┘ä┘è╪º┘ï. ┘ä╪º ╪¬┘é┘ä┘é┘è ╪¿╪┤╪ú┘å ╪¼┘ê╪»╪⌐ ╪º┘ä┘à┘å╪¬╪¼╪º╪¬ - ┘å╪¡┘å ┘å╪¬╪╣╪º┘à┘ä ┘ü┘é╪╖ ┘à╪╣ ╪º┘ä┘à╪º╪▒┘â╪º╪¬ ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐ ╪º┘ä╪ú╪╡┘ä┘è╪⌐.'],
                    ['num' => '02', 'icon' => 'fa-solid fa-truck-fast', 'title' => '╪┤╪¡┘å ╪│╪▒┘è╪╣ ┘ä┘â┘ä ┘ü┘ä╪│╪╖┘è┘å', 'desc' => '╪¬┘ê╪╡┘è┘ä ┘ä╪¼┘à┘è╪╣ ╪º┘ä┘à┘å╪º╪╖┘é ┘à┘å ╪¼┘å┘è┘å ╪Ñ┘ä┘ë ╪▒╪º┘à ╪º┘ä┘ä┘ç ┘ê╪º┘ä╪«┘ä┘è┘ä ┘ê╪║╪▓╪⌐╪î ┘à╪╣ ╪¬╪¬╪¿╪╣ ┘à╪¿╪º╪┤╪▒ ┘ä╪┤╪¡┘å╪¬┘â ╪¡╪¬┘ë ╪¿╪º╪¿ ┘à┘å╪▓┘ä┘â. ╪º╪╖┘ä╪¿┘è ╪º┘ä┘è┘ê┘à ┘ê╪º╪│╪¬┘ä┘à┘è ╪«┘ä╪º┘ä 24-48 ╪│╪º╪╣╪⌐.'],
                    ['num' => '03', 'icon' => 'fa-solid fa-headset', 'title' => '╪»╪╣┘à ┘è┘ê┘à┘è ╪º╪¡╪¬╪▒╪º┘ü┘è', 'desc' => '┘ü╪▒┘è┘é ┘à╪¬╪«╪╡╪╡ ╪¼╪º┘ç╪▓ ┘ä┘à╪│╪º╪╣╪»╪¬┘â ┘à┘å 9 ╪╡╪¿╪º╪¡╪º┘ï ╪¡╪¬┘ë 10 ┘à╪│╪º╪í┘ï ╪╣╪¿╪▒ ╪º┘ä┘ê╪º╪¬╪│╪º╪¿. ╪º╪│╪¬╪┤╪º╪▒╪º╪¬ ┘à╪¼╪º┘å┘è╪⌐ ┘ä╪º╪«╪¬┘è╪º╪▒ ╪º┘ä┘à┘å╪¬╪¼ ╪º┘ä┘à┘å╪º╪│╪¿ ┘ä┘å┘ê╪╣ ╪¿╪┤╪▒╪¬┘â.'],
                ];
            @endphp
            @foreach($valueCards as $card)
            <div class="value-card glass-panel rounded-[2rem] p-8 text-center group relative overflow-hidden transition-all duration-500">
                {{-- Top accent line --}}
                <div class="absolute top-0 right-8 left-8 h-[3px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: var(--gradient-primary);"></div>
                {{-- Background glow --}}
                <div class="absolute -left-10 -bottom-10 w-40 h-40 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-700" style="background: radial-gradient(circle, var(--brand-500) 0%, transparent 70%); filter: blur(50px);"></div>
                {{-- Number badge --}}
                <div class="absolute top-6 left-6 text-6xl font-black opacity-[0.04] group-hover:opacity-[0.08] transition-opacity duration-500 select-none" style="color: var(--brand-500);">{{ $card['num'] }}</div>
                {{-- Icon --}}
                <div class="relative z-10 w-16 h-16 rounded-2xl bg-brand-500/10 flex items-center justify-center mb-6 group-hover:bg-brand-500/20 group-hover:scale-110 transition-all duration-500 shadow-neon mx-auto">
                    <i class="{{ $card['icon'] }} text-2xl" style="color: var(--brand-500);"></i>
                </div>
                {{-- Content --}}
                <div class="relative z-10">
                    <h3 class="text-xl font-black mb-3" style="color: var(--ink);">{{ $card['title'] }}</h3>
                    <p class="text-ink-dim text-sm leading-relaxed">{{ $card['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

</div>

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
     SECTION 4: Tech Marquee Ticker
     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
<div class="py-10 border-y border-white/5 overflow-hidden flex whitespace-nowrap opacity-40 hover:opacity-70 transition-opacity">
    <div class="animate-marquee-rtl flex items-center gap-16 font-mono text-xs tracking-[0.2em] uppercase text-white/50">
        <span><i class="fa-solid fa-asterisk text-brand-500 text-[8px] mr-2"></i> ┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ 100%</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>╪┤╪¡┘å ╪│╪▒┘è╪╣ ┘ä┘â┘ä ┘ü┘ä╪│╪╖┘è┘å</span>
        <i class="fa-solid fa-circle text-[4px] text-accent-500"></i>
        <span>╪ú┘ü╪╢┘ä ┘à╪º╪▒┘â╪º╪¬ ╪º┘ä╪¬╪¼┘à┘è┘ä ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>╪º┘ä╪»┘ü╪╣ ╪╣┘å╪» ╪º┘ä╪º╪│╪¬┘ä╪º┘à</span>
        <i class="fa-solid fa-circle text-[4px] text-accent-500"></i>
        <span>╪»╪╣┘à ╪º╪¡╪¬╪▒╪º┘ü┘è ┘è┘ê┘à┘è</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>╪╣╪▒┘ê╪╢ ┘ê╪«╪╡┘ê┘à╪º╪¬ ╪¡╪╡╪▒┘è╪⌐</span>
        <i class="fa-solid fa-circle text-[4px] text-accent-500"></i>
        <span>╪¬┘ê╪╡┘è┘ä ┘ä╪¼┘à┘è╪╣ ╪º┘ä┘à┘å╪º╪╖┘é</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>┘à┘å╪¬╪¼╪º╪¬ ╪ú╪╡┘ä┘è╪⌐ 100%</span>
        <i class="fa-solid fa-circle text-[4px] text-accent-500"></i>
        <span>╪┤╪¡┘å ╪│╪▒┘è╪╣ ┘ä┘â┘ä ┘ü┘ä╪│╪╖┘è┘å</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>╪ú┘ü╪╢┘ä ┘à╪º╪▒┘â╪º╪¬ ╪º┘ä╪¬╪¼┘à┘è┘ä ╪º┘ä╪╣╪º┘ä┘à┘è╪⌐</span>
    </div>
</div>

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
     SECTION 5: More Products ΓÇö Horizontal Scroll
     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
@if($newProducts->isNotEmpty() || $featuredProducts->isNotEmpty())
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-12 flex items-end justify-between">
            <div class="text-right">
                <h2 class="text-3xl md:text-4xl font-black mb-2">┘ê╪╡┘ä ╪¡╪»┘è╪½╪º┘ï</h2>
                <p class="text-ink-dim text-sm">╪ú╪¡╪»╪½ ╪º┘ä┘à┘å╪¬╪¼╪º╪¬ ╪º┘ä╪ú╪╡┘ä┘è╪⌐ ┘ü┘è ┘à╪«╪¬╪¿╪▒ ╪º┘ä╪¼┘à╪º┘ä - ╪┤╪¡┘å ╪│╪▒┘è╪╣ ┘ê╪¬┘ê╪╡┘è┘ä ┘ä┘â┘ä ┘ü┘ä╪│╪╖┘è┘å</p>
            </div>
            <a href="{{ route('shop') }}?sort=newest" class="text-brand-500 font-bold text-sm hover:gap-3 flex items-center gap-1 transition-all">
                ╪╣╪▒╪╢ ╪º┘ä┘â┘ä <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
        </div>

        <div class="flex gap-6 overflow-x-auto hide-scroll pb-4" style="scroll-snap-type: x mandatory;">
            @php $scrollProducts = $newProducts->isNotEmpty() ? $newProducts : $featuredProducts; @endphp
            @foreach($scrollProducts->take(8) as $product)
            <a href="{{ route('product.show', $product->slug) }}"
               class="flex-shrink-0 w-[260px] glass-panel rounded-2xl overflow-hidden group border border-white/5 block transition-all duration-500 hover:-translate-y-2 hover:border-brand-500/30" style="scroll-snap-align: start;">
                <div class="relative h-[260px] overflow-hidden">
                    @if($product->main_image_url)
                    <img src="{{ $product->optimizedImageUrl(400, 400) }}" alt="{{ $product->name_ar }}" width="400" height="400"
                         class="w-full h-full object-contain filter brightness-75 group-hover:brightness-100 group-hover:scale-110 transition-all duration-700"
                         loading="lazy">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-surface-alt">
                        <i class="fa-solid fa-box text-4xl text-white/10"></i>
                    </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-surface/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute top-3 right-3">
                        <span class="pill-brand text-[10px] px-2 py-0.5">╪¼╪»┘è╪»</span>
                    </div>
                    <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                        <span class="bg-white style="color:#0f172a;" text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-bag-shopping text-[9px]"></i> ╪º┘â╪¬╪┤┘ü┘è ╪º┘ä┘à╪▓┘è╪»
                        </span>
                    </div>
                </div>
                <div class="p-5 text-right">
                    <h3 class="font-bold text-sm mb-1 line-clamp-1" style="color: var(--ink);">{{ $product->name_ar }}</h3>
                    @if($product->brand)
                    <p class="text-ink-dim text-xs mb-3">{{ $product->brand->name }}</p>
                    @endif
                    <span class="text-brand-500 font-black text-lg">{{ number_format($product->b2c_price, 0) }} Γé¬</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
     SECTION 6: Protocols / CTA Banner
     ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(var(--brand-500-rgb,255,42,133),0.06),transparent_70%)]"></div>
    <div class="max-w-5xl mx-auto px-4 text-center relative z-10">
        <div class="glass-panel rounded-[3rem] p-12 md:p-16 border border-white/5">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-8">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">╪º╪¿╪»╪ª┘è ╪▒╪¡┘ä╪¬┘â ╪º┘ä╪ó┘å</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-6">
                ┘à╪│╪¬╪╣╪»╪⌐ ┘ä╪º┘â╪¬╪┤╪º┘ü<br>
                <span class="gradient-text bg-[length:200%_auto]">╪▒┘ê╪¬┘è┘å┘â ╪º┘ä┘à╪½╪º┘ä┘è╪ƒ</span>
            </h2>
            <p class="text-ink-dim text-lg mb-10 max-w-2xl mx-auto font-light">
                ╪º┘å╪╢┘à┘è ╪Ñ┘ä┘ë ╪ó┘ä╪º┘ü ╪º┘ä╪╣┘à┘è┘ä╪º╪¬ ╪º┘ä╪│╪╣┘è╪»╪º╪¬ ┘ê╪º╪¿╪»╪ª┘è ╪▒╪¡┘ä╪⌐ ╪º┘ä╪╣┘å╪º┘è╪⌐ ╪¿╪¿╪┤╪▒╪¬┘â ┘à╪╣ ╪ú┘ü╪╢┘ä ╪º┘ä┘à┘å╪¬╪¼╪º╪¬ ╪º┘ä╪ú╪╡┘ä┘è╪⌐. ╪┤╪¡┘å ╪│╪▒┘è╪╣╪î ╪»┘ü╪╣ ╪ó┘à┘å╪î ┘ê╪»╪╣┘à ╪º╪¡╪¬╪▒╪º┘ü┘è ╪╣┘ä┘ë ┘à╪»╪º╪▒ ╪º┘ä╪ú╪│╪¿┘ê╪╣.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('shop') }}"
                   class="px-10 py-4 rounded-full font-black text-sm tracking-wide inline-flex items-center justify-center gap-2 shadow-neon hover:shadow-neon-strong transition-all"
                   style="background: var(--gradient-primary); color: white;">
                    ╪¬╪╡┘ü╪¡┘è ╪º┘ä┘à┘å╪¬╪¼╪º╪¬ <i class="fa-solid fa-arrow-left"></i>
                </a>
                <a href="{{ route('b2b') }}"
                   class="px-10 py-4 rounded-full font-bold text-sm border border-white/15 text-white hover:bg-white/5 transition-all inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-crown text-accent-500"></i> ╪¡┘ä┘ê┘ä ╪º┘ä╪¼┘à┘ä╪⌐ ┘ê╪º┘ä╪╡╪º┘ä┘ê┘å╪º╪¬
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
