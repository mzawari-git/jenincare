(function () {
    if (window._fingerprintLoaded) return;
    window._fingerprintLoaded = true;

    function getCanvasFingerprint() {
        try {
            var canvas = document.createElement('canvas');
            canvas.width = 200;
            canvas.height = 50;
            var ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillStyle = '#f60';
            ctx.fillRect(125, 1, 62, 20);
            ctx.fillStyle = '#069';
            ctx.fillText('شركة جنين للتجميل', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('FP', 4, 17);
            return canvas.toDataURL();
        } catch (e) {
            return null;
        }
    }

    function getWebGLFingerprint() {
        try {
            var canvas = document.createElement('canvas');
            var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) return null;
            var debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (!debugInfo) return null;
            return gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) + '|' +
                   gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
        } catch (e) {
            return null;
        }
    }

    function getAudioFingerprint() {
        try {
            var ctx = new (window.OfflineAudioContext || window.webkitOfflineAudioContext)(1, 44100, 44100);
            var osc = ctx.createOscillator();
            osc.type = 'triangle';
            osc.frequency.value = 10000;
            var comp = ctx.createDynamicsCompressor();
            comp.threshold.value = -50;
            comp.knee.value = 40;
            comp.ratio.value = 12;
            osc.connect(comp);
            comp.connect(ctx.destination);
            osc.start(0);
            return 'audio-available';
        } catch (e) {
            return null;
        }
    }

    function getScreenFingerprint() {
        return screen.width + 'x' + screen.height + 'x' + screen.colorDepth +
               '|' + (window.devicePixelRatio || 1);
    }

    function getTimezone() {
        try {
            return Intl.DateTimeFormat().resolvedOptions().timeZone;
        } catch (e) {
            return null;
        }
    }

    function getFonts() {
        var fonts = ['Arial', 'Arial Black', 'Arial Narrow', 'Calibri', 'Cambria',
            'Cambria Math', 'Comic Sans MS', 'Consolas', 'Courier New', 'Georgia',
            'Helvetica', 'Impact', 'Lucida Console', 'Lucida Sans Unicode',
            'Microsoft Sans Serif', 'Palatino Linotype', 'Segoe UI', 'Segoe UI Light',
            'Segoe UI Semibold', 'Tahoma', 'Times New Roman', 'Trebuchet MS',
            'Verdana', 'Webdings'];
        var available = [];
        var baseFonts = ['monospace', 'sans-serif', 'serif'];
        var testString = 'mmiiIIllOo09@# $%&*()_+=-[]{}|;:,.<>?/~`';
        var testSize = '72px';
        var testDiv = document.createElement('div');
        testDiv.style.position = 'absolute';
        testDiv.style.left = '-9999px';
        testDiv.style.top = '-9999px';
        testDiv.style.visibility = 'hidden';
        testDiv.style.fontSize = testSize;
        testDiv.innerHTML = testString;
        document.body.appendChild(testDiv);
        for (var i = 0; i < fonts.length; i++) {
            var detected = false;
            for (var j = 0; j < baseFonts.length; j++) {
                testDiv.style.fontFamily = fonts[i] + ',' + baseFonts[j];
                var width1 = testDiv.offsetWidth;
                testDiv.style.fontFamily = baseFonts[j];
                var width2 = testDiv.offsetWidth;
                if (width1 !== width2) {
                    detected = true;
                    break;
                }
            }
            if (detected) available.push(fonts[i]);
        }
        document.body.removeChild(testDiv);
        return available;
    }

    function getPlugins() {
        try {
            var plugins = [];
            for (var i = 0; i < navigator.plugins.length; i++) {
                plugins.push(navigator.plugins[i].name);
            }
            return plugins;
        } catch (e) {
            return [];
        }
    }

    function getHardwareConcurrency() {
        return navigator.hardwareConcurrency || null;
    }

    function getPlatform() {
        return navigator.platform || null;
    }

    function sha256(str) {
        if (window.crypto && window.crypto.subtle && window.crypto.subtle.digest) {
            var encoder = new TextEncoder();
            return crypto.subtle.digest('SHA-256', encoder.encode(str))
                .then(function (hash) {
                    return Array.from(new Uint8Array(hash))
                        .map(function (b) { return b.toString(16).padStart(2, '0'); })
                        .join('');
                });
        }
        return Promise.resolve(null);
    }

    function collectSignals() {
        return {
            canvas: getCanvasFingerprint(),
            webgl: getWebGLFingerprint(),
            audio: getAudioFingerprint(),
            screen: getScreenFingerprint(),
            timezone: getTimezone(),
            fonts: getFonts(),
            plugins: getPlugins(),
            hardwareConcurrency: getHardwareConcurrency(),
            platform: getPlatform(),
            languages: navigator.languages || [navigator.language],
            cookieEnabled: navigator.cookieEnabled,
            doNotTrack: navigator.doNotTrack,
            touchSupport: 'ontouchstart' in window,
        };
    }

    function getCookie(name) {
        var match = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return match ? match.pop() : null;
    }

    function sendFingerprint() {
        var signals = collectSignals();
        var signalString = JSON.stringify(signals);

        sha256(signalString).then(function (hash) {
            var payload = {
                uuid: getCookie('_juuid'),
                fingerprint_hash: hash,
                fingerprint_data: signals,
                url: window.location.href,
                referer: document.referrer || null,
            };

            var bp = window.basePath || (function() {
                var m = window.location.pathname.match(/^(.+)\/(public|PUBLIC)\//);
                return m ? m[1] + '/' + m[2] : '';
            })();
            if (navigator.sendBeacon) {
                navigator.sendBeacon(bp + '/api/track/fingerprint', new Blob([JSON.stringify(payload)], { type: 'application/json' }));
            } else {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', bp + '/api/track/fingerprint', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.send(JSON.stringify(payload));
            }
        });
    }

    if (document.readyState === 'complete') {
        sendFingerprint();
    } else {
        window.addEventListener('load', sendFingerprint);
    }
})();
