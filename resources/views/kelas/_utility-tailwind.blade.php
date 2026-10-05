        {{-- Utility Tailwind untuk halaman /kelas. Di-generate dari hasil
             "vite build" lokal; jangan diedit tangan.
             Lihat resources/views/kelas/index.blade.php. --}}
.-my-1 { margin-block:calc(var(--spacing) * -1) }
.mt-0\.5 { margin-top:calc(var(--spacing) * .5) }
.mt-1\.5 { margin-top:calc(var(--spacing) * 1.5) }
.-ml-1\.5 { margin-left:calc(var(--spacing) * -1.5) }
.w-\[190px\] { width:190px }
.w-\[150px\] { width:150px }
.w-\[70px\] { width:70px }
.max-w-\[180px\] { max-width:180px }
.min-w-\[26px\] { min-width:26px }
.min-w-\[640px\] { min-width:640px }
.gap-1\.5 { gap:calc(var(--spacing) * 1.5) }
.gap-2\.5 { gap:calc(var(--spacing) * 2.5) }
.border-indigo-100 { border-color:var(--color-indigo-100) }
.border-indigo-200 { border-color:var(--color-indigo-200) }
.bg-amber-100 { background-color:var(--color-amber-100) }
.bg-cyan-100 { background-color:var(--color-cyan-100) }
.bg-indigo-50 { background-color:var(--color-indigo-50) }
.bg-slate-700 { background-color:var(--color-slate-700) }
.bg-slate-900\/45 { background-color:#0f172b73 }
.bg-white\/20 { background-color:#fff3 }
.px-2\.5 { padding-inline:calc(var(--spacing) * 2.5) }
.py-0\.5 { padding-block:calc(var(--spacing) * .5) }
.py-1\.5 { padding-block:calc(var(--spacing) * 1.5) }
.py-2\.5 { padding-block:calc(var(--spacing) * 2.5) }
.pt-4 { padding-top:calc(var(--spacing) * 4) }
.pt-6 { padding-top:calc(var(--spacing) * 6) }
.pb-5 { padding-bottom:calc(var(--spacing) * 5) }
.pb-10 { padding-bottom:calc(var(--spacing) * 10) }
.text-\[10px\] { font-size:10px }
.text-\[11px\] { font-size:11px }
.text-\[12px\] { font-size:12px }
.text-\[13px\] { font-size:13px }
.text-\[14px\] { font-size:14px }
.text-\[15px\] { font-size:15px }
.text-amber-600 { color:var(--color-amber-600) }
.text-cyan-600 { color:var(--color-cyan-600) }
.text-indigo-700 { color:var(--color-indigo-700) }
.text-indigo-800 { color:var(--color-indigo-800) }
.text-slate-500 { color:var(--color-slate-500) }
.text-slate-600 { color:var(--color-slate-600) }
.text-slate-800 { color:var(--color-slate-800) }
.accent-emerald-600 { accent-color:var(--color-emerald-600) }
.focus\:ring-2:focus { --tw-ring-shadow:var(--tw-ring-inset,) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color,currentcolor);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow) }
.focus\:ring-indigo-300:focus { --tw-ring-color:var(--color-indigo-300) }
.focus\:outline-none:focus { --tw-outline-style:none;outline-style:none }
.active\:scale-95:active { --tw-scale-x:95%;--tw-scale-y:95%;--tw-scale-z:95%;scale:var(--tw-scale-x) var(--tw-scale-y) }
.disabled\:cursor-not-allowed:disabled { cursor:not-allowed }
.disabled\:opacity-50:disabled { opacity:.5 }
@supports (color:color-mix(in lab, red, red)) {
.bg-slate-900\/45 { background-color:color-mix(in oklab, var(--color-slate-900) 45%, transparent) }
.bg-white\/20 { background-color:color-mix(in oklab, var(--color-white) 20%, transparent) }
}.bg-white\/95 { background-color:#fffffff2 }
.bg-white\/95 { background-color:color-mix(in oklab, var(--color-white) 95%, transparent) }
}
@media (hover:hover) {
.hover\:bg-emerald-600:hover { background-color:var(--color-emerald-600) }
.hover\:bg-indigo-50:hover { background-color:var(--color-indigo-50) }
.hover\:bg-indigo-100:hover { background-color:var(--color-indigo-100) }
.hover\:bg-indigo-600:hover { background-color:var(--color-indigo-600) }
.hover\:bg-red-50:hover { background-color:var(--color-red-50) }
.hover\:bg-red-100:hover { background-color:var(--color-red-100) }
.hover\:bg-red-600:hover { background-color:var(--color-red-600) }
.hover\:bg-slate-50:hover { background-color:var(--color-slate-50) }
.hover\:bg-slate-200:hover { background-color:var(--color-slate-200) }
.hover\:bg-slate-800:hover { background-color:var(--color-slate-800) }
.hover\:text-indigo-700:hover { color:var(--color-indigo-700) }
.hover\:text-slate-600:hover { color:var(--color-slate-600) }
.hover\:text-slate-900:hover { color:var(--color-slate-900) }
.hover\:text-teal-600:hover { color:var(--color-teal-600) }
}
@media (hover:hover) {
@supports (color:color-mix(in lab, red, red)) {
}.hover\:bg-slate-100:hover { background-color:var(--color-slate-100) }
}
}
@media (width>=40rem) {
.sm\:col-span-2 { grid-column:span 2/span 2 }
.sm\:col-span-4 { grid-column:span 4/span 4 }
.sm\:h-14 { height:calc(var(--spacing) * 14) }
.sm\:w-14 { width:calc(var(--spacing) * 14) }
.sm\:grid-cols-4 { grid-template-columns:repeat(4,minmax(0,1fr)) }
.sm\:px-6 { padding-inline:calc(var(--spacing) * 6) }
.sm\:text-xl { font-size:var(--text-xl);line-height:var(--tw-leading,var(--text-xl--line-height)) }
.sm\:text-xs { font-size:var(--text-xs);line-height:var(--tw-leading,var(--text-xs--line-height)) }
}
@media (width>=64rem) {
.lg\:px-8 { padding-inline:calc(var(--spacing) * 8) }
}
