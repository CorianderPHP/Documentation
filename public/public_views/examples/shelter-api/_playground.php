<section class="mt-8 border-t border-dark-green/10 pt-6 dark:border-peach/15" data-api-playground>
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div>
            <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-peach">Request</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-[8rem_1fr]">
                <label class="text-sm font-semibold text-black/70 dark:text-white/70" for="api-playground-preset">Endpoint</label>
                <select id="api-playground-preset" class="rounded-md border border-dark-green/15 bg-true-white px-3 py-2 text-sm text-black outline-none focus:border-dark-green dark:border-peach/20 dark:bg-true-black dark:text-white dark:focus:border-peach" data-api-preset>
                    <option value="list">GET animals</option>
                    <option value="show">GET animal by id</option>
                    <option value="create">POST animal</option>
                    <option value="update">PATCH animal</option>
                    <option value="delete">DELETE animal</option>
                    <option value="species">GET species</option>
                    <option value="shelters">GET shelters</option>
                </select>
                <label class="text-sm font-semibold text-black/70 dark:text-white/70" for="api-playground-method">Method</label>
                <input id="api-playground-method" class="rounded-md border border-dark-green/15 bg-true-white px-3 py-2 text-sm text-black outline-none dark:border-peach/20 dark:bg-true-black dark:text-white" data-api-method readonly />
                <label class="text-sm font-semibold text-black/70 dark:text-white/70" for="api-playground-url">URL</label>
                <input id="api-playground-url" class="rounded-md border border-dark-green/15 bg-true-white px-3 py-2 text-sm text-black outline-none focus:border-dark-green dark:border-peach/20 dark:bg-true-black dark:text-white dark:focus:border-peach" data-api-url />
            </div>
            <label class="mt-5 block text-sm font-semibold text-black/70 dark:text-white/70" for="api-playground-body">JSON body</label>
            <textarea id="api-playground-body" class="mt-2 min-h-52 w-full rounded-md border border-dark-green/15 bg-true-white p-3 font-mono text-sm text-black outline-none focus:border-dark-green dark:border-peach/20 dark:bg-true-black dark:text-white dark:focus:border-peach" data-api-body spellcheck="false"></textarea>
            <button type="button" class="mt-4 rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white transition hover:-translate-y-0.5 hover:bg-dark-green/90 focus:outline-none focus:ring-2 focus:ring-dark-green/30 dark:bg-peach dark:text-black dark:hover:bg-peach/90 dark:focus:ring-peach/30" data-api-send>Send request</button>
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-peach">Response</p>
            <div class="mt-4 rounded-md border border-dark-green/15 bg-true-white dark:border-peach/20 dark:bg-true-black">
                <div class="border-b border-dark-green/10 px-4 py-3 text-sm font-semibold text-black/70 dark:border-peach/15 dark:text-white/70" data-api-status>Status: waiting</div>
                <pre class="min-h-96 overflow-x-auto p-4 text-sm text-black dark:text-white"><code data-api-response>{
  "message": "Choose an endpoint and send a request."
}</code></pre>
            </div>
        </div>
    </div>
</section>
