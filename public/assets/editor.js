(function () {
  const rail = document.getElementById('pages-rail');
  const blocksHost = document.getElementById('blocks-host');
  const addPageBtn = document.getElementById('add-page');      // may be absent (rail uses #rail-add)
  const addTableBtn = document.getElementById('add-table');    // may be hidden on cover
  const addContentBtn = document.getElementById('add-content');// may be hidden on cover
  const itemsField = document.getElementById('items-json');    // MUST exist inside the <form>
  const coverSection = document.getElementById('cover-section');
  const form = document.getElementById('editor-form');         // MUST match your <form id>

  // guard: avoid hard crashes if any required element is missing
  function safeSerialize() {
    if (!itemsField) return; // nothing to put value into
    try { itemsField.value = JSON.stringify(items); } catch (e) { /* noop */ }
  }

  // Default: ONLY the Cover page (unless server seeded items)
  let items = Array.isArray(window.__INITIAL_ITEMS__) && window.__INITIAL_ITEMS__.length
    ? window.__INITIAL_ITEMS__
    : [{ type: 'page', label: 'Cover & Introduction', body: { title: 'Cover & Introduction' } }];

  let currentPageIndex = 0; // start at first page

  function pageIndices() {
    const idxs = [];
    for (let i = 0; i < items.length; i++) if (items[i].type === 'page') idxs.push(i);
    return idxs;
  }

  function childrenOf(pageIdx) {
    const kids = [];
    for (let i = pageIdx + 1; i < items.length && items[i].type !== 'page'; i++) kids.push({ i, item: items[i] });
    return kids;
  }

  function renderRail() {
    if (!rail) return;
    rail.innerHTML = '';
    const idxs = pageIndices();

    idxs.forEach((pi, n) => {
      const chip = document.createElement('div');
      chip.className = 'page-chip' + (pi === currentPageIndex ? ' active' : '');
      chip.onclick = () => { currentPageIndex = pi; render(); };

      const canDelete = n !== 0; // first page non-deletable
      chip.innerHTML = `
        <div class="num">${n + 1}</div>
        <div style="flex:1">
          <div style="font-weight:700">${items[pi].label || 'Untitled'}</div>
          <div class="badges">
            ${childrenOf(pi).map(({ item }) => `<span class="badge">${item.type === 'table' ? 'Table—' : 'Content—'}</span>`).join('')}
          </div>
        </div>
        ${canDelete ? `<button class="btn danger" type="button" style="padding:4px 8px" data-del="${pi}">Delete</button>` : ''}
      `;
      rail.appendChild(chip);
    });

    const add = document.createElement('div');
    add.className = 'controls';
    add.innerHTML = '<button class="btn" type="button" id="rail-add">+ Add Page</button>';
    rail.appendChild(add);

    const railAdd = rail.querySelector('#rail-add');
    if (railAdd) railAdd.onclick = addPage;

    rail.querySelectorAll('button[data-del]').forEach(btn => {
      btn.onclick = (e) => {
        e.stopPropagation();
        const idx = parseInt(btn.getAttribute('data-del') || '-1', 10);
        if (!Number.isFinite(idx) || idx < 0) return;
        if (confirm('Delete this page and its blocks?')) {
          while (items[idx + 1] && items[idx + 1].type !== 'page') items.splice(idx + 1, 1);
          items.splice(idx, 1);
          currentPageIndex = 0;
          render();
        }
      };
    });
  }

  function renderBlocks() {
    if (!blocksHost) return;
    const kids = childrenOf(currentPageIndex);
    blocksHost.innerHTML = '';

    if (coverSection) coverSection.style.display = (currentPageIndex === 0) ? '' : 'none';

    // On cover page: hide add-table/content controls
    if (currentPageIndex === 0) {
      if (addTableBtn) addTableBtn.style.display = 'none';
      if (addContentBtn) addContentBtn.style.display = 'none';
      return;
    } else {
      if (addTableBtn) addTableBtn.style.display = '';
      if (addContentBtn) addContentBtn.style.display = '';
    }

    // Page Title
    const h = document.createElement('div');
    h.className = 'field';
    h.innerHTML = '<label>Page Title</label><input type="text" id="page-title" value="' + (items[currentPageIndex].label || '') + '">';
    blocksHost.appendChild(h);
    const pt = h.querySelector('#page-title');
    if (pt) pt.oninput = (e) => { items[currentPageIndex].label = e.target.value; renderRail(); safeSerialize(); };

    // Child blocks
    kids.forEach(({ i, item }) => {
      if (item.type === 'table') {
        const wrap = document.createElement('div');
        wrap.className = 'table-editor';
        wrap.innerHTML = `
          <div class="section-title">Table</div>
          <div class="field"><label>Table title (optional)</label>
            <input type="text" value="${(item.body && item.body.title) || ''}" data-key="title"></div>
          <div class="controls">
            <button class="btn" type="button" data-addcol>+ Add Column</button>
            <button class="btn" type="button" data-addrow>+ Add Row</button>
            <button class="btn danger" type="button" data-delete>Delete Table</button>
          </div>
          <table><thead><tr></tr></thead><tbody></tbody></table>
        `;

        const cols = (item.body && Array.isArray(item.body.columns) ? item.body.columns.slice() : ['label', 'content']);
        const rows = (item.body && Array.isArray(item.body.rows) ? item.body.rows.map(r => Array.isArray(r) ? r.slice() : [String(r)]) : [['Course title', '']]);

        const thead = wrap.querySelector('thead tr');
        cols.forEach(c => { const th = document.createElement('th'); th.textContent = c; thead.appendChild(th); });

        const tbody = wrap.querySelector('tbody');
        rows.forEach((r, ri) => {
          const tr = document.createElement('tr');
          cols.forEach((c, ci) => {
            const td = document.createElement('td');
            td.innerHTML = `<input type="text" value="${r[ci] || ''}">`;
            td.querySelector('input').oninput = (e) => {
              item.body = item.body || {};
              item.body.columns = cols;
              item.body.rows = rows;
              rows[ri][ci] = e.target.value;
              safeSerialize();
            };
            tr.appendChild(td);
          });
          tbody.appendChild(tr);
        });

        const addCol = wrap.querySelector('[data-addcol]');
        if (addCol) addCol.onclick = () => { cols.push('col' + (cols.length + 1)); rows.forEach(r => r.push('')); item.body = Object.assign({}, item.body, { columns: cols, rows }); renderBlocks(); safeSerialize(); };

        const addRow = wrap.querySelector('[data-addrow]');
        if (addRow) addRow.onclick = () => { rows.push(cols.map(() => '')); item.body = Object.assign({}, item.body, { columns: cols, rows }); renderBlocks(); safeSerialize(); };

        const del = wrap.querySelector('[data-delete]');
        if (del) del.onclick = () => { items.splice(i, 1); render(); safeSerialize(); };

        const titleInp = wrap.querySelector('[data-key="title"]');
        if (titleInp) titleInp.oninput = (e) => { item.body = item.body || {}; item.body.title = e.target.value; safeSerialize(); };

        blocksHost.appendChild(wrap);
      } else if (item.type === 'content') {
        const wrap = document.createElement('div');
        wrap.innerHTML = `
          <div class="section-title">Course Content</div>
          <div class="field"><label>Subtitle</label><input type="text" value="${(item.body && item.body.subTitle) || ''}" data-k="subTitle"></div>
          <div class="field"><label>Content</label><textarea data-k="richText">${(item.body && item.body.richText) || ''}</textarea></div>
          <div class="controls"><button class="btn danger" type="button" data-del>Delete</button></div>
          <hr class="sep"/>
        `;
        const sub = wrap.querySelector('[data-k="subTitle"]');
        if (sub) sub.oninput = (e) => { item.body = item.body || {}; item.body.subTitle = e.target.value; safeSerialize(); };
        const rich = wrap.querySelector('[data-k="richText"]');
        if (rich) rich.oninput = (e) => { item.body = item.body || {}; item.body.richText = e.target.value; safeSerialize(); };
        const del = wrap.querySelector('[data-del]');
        if (del) del.onclick = () => { items.splice(i, 1); render(); safeSerialize(); };
        blocksHost.appendChild(wrap);
      }
    });
  }

  function addPage() {
    const at = currentPageIndex + childrenOf(currentPageIndex).length + 1;
    items.splice(at, 0, { type: 'page', label: 'New Page', body: { title: 'New Page' } });
    currentPageIndex = at;
    render();
  }

  function addTable() {
    const at = currentPageIndex + childrenOf(currentPageIndex).length + 1;
    items.splice(at, 0, { type: 'table', label: 'Table', body: { title: '', columns: ['label', 'content'], rows: [['Course title', '']] } });
    render();
  }

  function addContent() {
    const at = currentPageIndex + childrenOf(currentPageIndex).length + 1;
    items.splice(at, 0, { type: 'content', label: 'Course Content', body: { subTitle: '', richText: '' } });
    render();
  }

  // Only attach if the buttons exist
  if (addPageBtn) addPageBtn.onclick = addPage;
  if (addTableBtn) addTableBtn.onclick = addTable;
  if (addContentBtn) addContentBtn.onclick = addContent;

  function render() { renderRail(); renderBlocks(); safeSerialize(); }

  if (form) form.addEventListener('submit', safeSerialize);

  render();
})();
