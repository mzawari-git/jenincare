<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');

// Create a simple HTML page that stores the content in localStorage
$html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Processing...</title></head><body>';
$html .= '<h2>جاري تجهيز المحتوى...</h2>';
$html .= '<div id="status"></div>';
$html .= '<script>';
$html .= 'const content = ' . json_encode($content, JSON_UNESCAPED_UNICODE) . ';';
$html .= 'const chunkSize = 10000;';
$html .= 'const chunks = [];';
$html .= 'for (let i = 0; i < content.length; i += chunkSize) {';
$html .= '    const key = "a12c_" + Math.floor(i / chunkSize);';
$html .= '    localStorage.setItem(key, content.substring(i, i + chunkSize));';
$html .= '    chunks.push(key);';
$html .= '}';
$html .= 'localStorage.setItem("a12_chunks", JSON.stringify(chunks));';
$html .= 'document.getElementById("status").textContent = "تم تخزين " + chunks.length + " أجزاء. العودة إلى صفحة التحرير...";';
$html .= 'setTimeout(() => { window.location.href = "/admin/blog/12/edit"; }, 1500);';
$html .= '</script></body></html>';

// Save to file also
file_put_contents('C:\Users\Home\Downloads\article12_loader.html', $html);

// Create base64 data URI
$base64 = base64_encode($html);
echo "data:text/html;base64," . $base64;
