<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
$chunks = str_split($content, 5000);

echo "// Run this after navigating to the admin edit page\n";
echo "// Step 1: Store all chunks\n";
foreach ($chunks as $i => $chunk) {
    $safe = json_encode($chunk);
    echo "localStorage.setItem('a12c_$i', $safe);\n";
}
echo "\n// Step 2: Concatenate and update\n";
echo "const ta = document.querySelector('[name=\"content_ar\"]');\n";
echo "let content = '';\n";
for ($i = 0; $i < count($chunks); $i++) {
    echo "content += localStorage.getItem('a12c_$i');\n";
}
echo "ta.value = content;\n";
echo "ta.dispatchEvent(new Event('input', {bubbles: true}));\n";
echo "// Submit with image\n";
echo "const fd = new FormData(document.querySelectorAll('form')[1]);\n";
echo "fd.set('content_ar', content);\n";
echo "const resp = await fetch('https://jenincare.shop/admin/blog/12', {\n";
echo "  method: 'POST',\n";
echo "  headers: {\n";
echo "    'X-CSRF-TOKEN': document.querySelector('[name=\"_token\"]').value,\n";
echo "    'Accept': 'application/json'\n";
echo "  },\n";
echo "  body: fd\n";
echo "});\n";
echo "console.log('Done:', resp.redirected ? 'Redirected to ' + resp.url : await resp.text());\n";
