// coriander-version:4903cf92371b77ff430f193a721425b8b144f64a0d8385910007cd17153f02b9
import{a as i}from"../_chunks/chunk-CIBRENT3.js";i();document.querySelectorAll("[data-scroll-memory]").forEach(t=>{let n=t.dataset.scrollMemory?.trim();if(!n)return;let r=`scroll:${n}`,o=window.sessionStorage.getItem(r);o!==null&&(t.scrollTop=Number.parseInt(o,10)||0),t.addEventListener("scroll",()=>{window.sessionStorage.setItem(r,String(t.scrollTop))},{passive:!0})});var c=document.querySelector("#documentation-search"),a=document.querySelector("#documentation-search-results"),u=document.querySelector('input[name="scope"]');if(c&&a){let t=null,n=()=>{let r=c.value.trim();if(r.length<2)return;t?.abort(),t=new AbortController;let o=u?.value??"all";fetch(`/api/documentation/search?q=${encodeURIComponent(r)}&scope=${encodeURIComponent(o)}`,{signal:t.signal}).then(e=>e.ok?e.json():null).then(e=>{e?.results&&(a.innerHTML=e.results.length===0?'<p class="py-5 text-black/70 dark:text-white/70">No documentation pages matched this search scope.</p>':e.results.map(s=>`
              <a href="/documentation/${l(s.slug)}" class="block py-5">
                <div class="flex flex-wrap items-center gap-2">
                  <h2 class="font-concert-one text-2xl text-dark-green dark:text-mint">${l(s.title)}</h2>
                  <span class="rounded-full border border-dark-green/15 px-2 py-0.5 text-xs font-semibold text-black/55 dark:border-mint/20 dark:text-white/55">${l(s.section)}</span>
                </div>
                <p class="mt-2 text-sm leading-6 text-black/65 dark:text-white/65">${l(s.excerpt)}</p>
              </a>
            `).join(""),window.CorianderCodeHighlighter?.highlightAll())}).catch(e=>{e instanceof DOMException&&e.name})};c.addEventListener("input",n)}function l(t){let n=document.createElement("span");return n.textContent=t,n.innerHTML}
