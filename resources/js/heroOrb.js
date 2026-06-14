// Hero "energy orb" — a tiny hand-written WebGL shader (no three.js).
// Breathing green plasma glow + orbiting sparks + subtle mouse parallax.
// Degrades gracefully: if WebGL is unavailable the canvas stays empty and the
// CSS glow/rings behind it still look good. Honors prefers-reduced-motion.

const VERT = `
attribute vec2 p;
void main() { gl_Position = vec4(p, 0.0, 1.0); }
`;

const FRAG = `
precision mediump float;
uniform vec2  uResolution;
uniform float uTime;
uniform vec2  uMouse;

float hash(vec2 p) { return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }

float noise(vec2 p) {
    vec2 i = floor(p), f = fract(p);
    vec2 u = f * f * (3.0 - 2.0 * f);
    return mix(mix(hash(i + vec2(0.0, 0.0)), hash(i + vec2(1.0, 0.0)), u.x),
               mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x), u.y);
}

float fbm(vec2 p) {
    float v = 0.0, a = 0.5;
    for (int i = 0; i < 5; i++) { v += a * noise(p); p *= 2.0; a *= 0.5; }
    return v;
}

void main() {
    vec2 uv = (gl_FragCoord.xy - 0.5 * uResolution.xy) / uResolution.y;
    uv -= uMouse * 0.05;                       // parallax drift
    float r = length(uv);

    float breath = 0.5 + 0.5 * sin(uTime * 0.9);

    // domain-warped flowing energy
    vec2 q = vec2(fbm(uv * 2.2 + uTime * 0.13), fbm(uv * 2.2 - uTime * 0.11));
    float energy = fbm(uv * 3.0 + q * 1.6 + uTime * 0.08);

    float radius = 0.40 + 0.05 * breath;
    float core = smoothstep(radius, 0.0, r);   // bright centre
    float halo = smoothstep(0.95, 0.0, r) * 0.45;

    float intensity = core * (0.45 + 0.9 * energy) + halo * (0.35 + 0.4 * energy);
    intensity *= (0.75 + 0.45 * breath);

    vec3 green = vec3(0.427, 0.745, 0.18);     // #6DBE2E
    vec3 col = mix(green, vec3(0.72, 1.0, 0.46), smoothstep(0.22, 0.0, r)) * intensity;

    // orbiting sparks
    for (int i = 0; i < 6; i++) {
        float fi = float(i);
        float a = uTime * 0.45 + fi * 1.0472;
        float rad = 0.30 + 0.05 * sin(uTime * 0.7 + fi);
        vec2 sp = vec2(cos(a), sin(a)) * rad;
        float d = length(uv - sp);
        col += green * smoothstep(0.018, 0.0, d) * (0.5 + 0.5 * sin(uTime * 2.0 + fi));
    }

    gl_FragColor = vec4(col, clamp(intensity, 0.0, 1.0));
}
`;

export default () => ({
    raf: null,
    gl: null,
    u: null,
    mouse: { x: 0, y: 0 },
    target: { x: 0, y: 0 },
    startedAt: 0,

    init() {
        const canvas = this.$refs.canvas;
        if (!canvas) return;

        const gl = canvas.getContext('webgl', { alpha: true, premultipliedAlpha: false, antialias: true });
        if (!gl) return; // no WebGL → CSS fallback remains

        const program = this.buildProgram(gl, VERT, FRAG);
        if (!program) return;
        gl.useProgram(program);
        this.gl = gl;

        const buffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
        const loc = gl.getAttribLocation(program, 'p');
        gl.enableVertexAttribArray(loc);
        gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

        this.u = {
            res: gl.getUniformLocation(program, 'uResolution'),
            time: gl.getUniformLocation(program, 'uTime'),
            mouse: gl.getUniformLocation(program, 'uMouse'),
        };

        gl.enable(gl.BLEND);
        gl.blendFunc(gl.SRC_ALPHA, gl.ONE); // additive glow

        this.resize = () => {
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.max(1, Math.floor(canvas.clientWidth * dpr));
            canvas.height = Math.max(1, Math.floor(canvas.clientHeight * dpr));
            gl.viewport(0, 0, canvas.width, canvas.height);
        };
        this.resize();
        window.addEventListener('resize', this.resize);

        this.onMove = (e) => {
            this.target.x = (e.clientX / window.innerWidth) * 2 - 1;
            this.target.y = (e.clientY / window.innerHeight) * 2 - 1;
        };
        window.addEventListener('pointermove', this.onMove, { passive: true });

        const draw = (time) => {
            gl.uniform2f(this.u.res, canvas.width, canvas.height);
            gl.uniform1f(this.u.time, time);
            gl.uniform2f(this.u.mouse, this.mouse.x, this.mouse.y);
            gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
        };

        // Respect reduced-motion: render one calm static frame, no loop.
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            draw(0);
            return;
        }

        this.startedAt = performance.now();
        const render = () => {
            this.mouse.x += (this.target.x - this.mouse.x) * 0.05;
            this.mouse.y += (this.target.y - this.mouse.y) * 0.05;
            draw((performance.now() - this.startedAt) / 1000);
            this.raf = requestAnimationFrame(render);
        };

        this.onVisibility = () => {
            if (document.hidden) { cancelAnimationFrame(this.raf); this.raf = null; }
            else if (!this.raf) { this.raf = requestAnimationFrame(render); }
        };
        document.addEventListener('visibilitychange', this.onVisibility);
        this.raf = requestAnimationFrame(render);
    },

    buildProgram(gl, vsrc, fsrc) {
        const compile = (type, src) => {
            const sh = gl.createShader(type);
            gl.shaderSource(sh, src);
            gl.compileShader(sh);
            if (!gl.getShaderParameter(sh, gl.COMPILE_STATUS)) {
                console.warn('heroOrb shader:', gl.getShaderInfoLog(sh));
                return null;
            }
            return sh;
        };
        const vs = compile(gl.VERTEX_SHADER, vsrc);
        const fs = compile(gl.FRAGMENT_SHADER, fsrc);
        if (!vs || !fs) return null;
        const prog = gl.createProgram();
        gl.attachShader(prog, vs);
        gl.attachShader(prog, fs);
        gl.linkProgram(prog);
        if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
            console.warn('heroOrb link:', gl.getProgramInfoLog(prog));
            return null;
        }
        return prog;
    },

    destroy() {
        if (this.raf) cancelAnimationFrame(this.raf);
        if (this.resize) window.removeEventListener('resize', this.resize);
        if (this.onMove) window.removeEventListener('pointermove', this.onMove);
        if (this.onVisibility) document.removeEventListener('visibilitychange', this.onVisibility);
        const lose = this.gl && this.gl.getExtension('WEBGL_lose_context');
        if (lose) lose.loseContext();
    },
});
