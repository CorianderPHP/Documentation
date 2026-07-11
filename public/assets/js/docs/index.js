"use strict";(()=>{document.querySelectorAll("[data-scroll-memory]").forEach(e=>{let n=e.dataset.scrollMemory?.trim();if(!n)return;let r=`scroll:${n}`,o=window.sessionStorage.getItem(r);o!==null&&(e.scrollTop=Number.parseInt(o,10)||0),e.addEventListener("scroll",()=>{window.sessionStorage.setItem(r,String(e.scrollTop))},{passive:!0})});var c=document.querySelector("#docs-search"),a=document.querySelector("#docs-search-results"),i=document.querySelector('input[name="scope"]');if(c&&a){let e=null,n=()=>{let r=c.value.trim();if(r.length<2)return;e?.abort(),e=new AbortController;let o=i?.value??"all";fetch(`/api/docs/search?q=${encodeURIComponent(r)}&scope=${encodeURIComponent(o)}`,{signal:e.signal}).then(t=>t.ok?t.json():null).then(t=>{t?.results&&(a.innerHTML=t.results.length===0?'<p class="py-5 text-black/70 dark:text-white/70">No documentation pages matched this search scope.</p>':t.results.map(s=>`
              <a href="/docs/${l(s.slug)}" class="block py-5">
                <div class="flex flex-wrap items-center gap-2">
                  <h2 class="font-concert-one text-2xl text-dark-green dark:text-mint">${l(s.title)}</h2>
                  <span class="rounded-full border border-dark-green/15 px-2 py-0.5 text-xs font-semibold text-black/55 dark:border-mint/20 dark:text-white/55">${l(s.section)}</span>
                </div>
                <p class="mt-2 text-sm leading-6 text-black/65 dark:text-white/65">${l(s.excerpt)}</p>
              </a>
            `).join(""),window.CorianderCodeHighlighter?.highlightAll())}).catch(t=>{t instanceof DOMException&&t.name})};c.addEventListener("input",n)}function l(e){let n=document.createElement("span");return n.textContent=e,n.innerHTML}})();
