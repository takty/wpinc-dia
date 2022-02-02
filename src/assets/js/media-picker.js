/**
 * Media Picker
 *
 * @author Takuto Yanagida
 * @version 2022-02-02
 */

function wpinc_media_picker_init(key) {
	const body    = document.querySelector(`.wpinc-dia-media-picker#${key}`);
	const add_row = body.getElementsByClassName('add_row')[0];
	const add_btn = add_row.querySelector('.button.add');

	const tbl   = body.getElementsByClassName('table')[0];
	const items = tbl.querySelectorAll('.item:not(.template)');
	const temp  = tbl.querySelector('.item.template');

	jQuery(tbl).sortable();
	jQuery(tbl).sortable('option', {
		axis       : 'y',
		containment: 'parent',
		cursor     : 'move',
		handle     : '.handle',
		items      : '> .item',
		placeholder: '.item-placeholder',
	});
	for (const it of items) assign_event_listener(it);
	setMediaPicker(add_btn, false, (t, fs) => fs.forEach(f => add_new_item(f)), { multiple: true, title: add_btn.innerText });


	// -------------------------------------------------------------------------


	function add_new_item(f) {
		const it = temp.cloneNode(true);
		set_item(it, f);
		it.classList.remove('template');
		tbl.insertBefore(it, add_row);
		assign_event_listener(it);
	}

	function set_item(it, f) {
		it.querySelector(`[name='${key}[media]']`).value    = f.id;
		it.querySelector(`[name='${key}[url]']`).value      = f.url;
		it.querySelector(`[name='${key}[title]']`).value    = f.title;
		it.querySelector(`[name='${key}[filename]']`).value = f.filename;
		it.getElementsByClassName('filename')[0].innerText  = f.filename;
	}

	function assign_event_listener(it) {
		const del_btn = it.getElementsByClassName('delete')[0];
		const sel_btn = it.getElementsByClassName('select')[0];
		const opener  = it.getElementsByClassName('opener')[0];

		del_btn.addEventListener('click', (e) => {
			if (e.target.checked) {
				it.classList.add('is-deleted');
			} else {
				it.classList.remove('is-deleted');
			}
		});
		opener.addEventListener('click', () => {
			const url = it.querySelector(`[name='${key}[url]']`).value;
			if (url) window.open(url);
		});
		setMediaPicker(sel_btn, false, (t, f) => set_item(it, f), { multiple: false, title: sel_btn.innerText });
	}
}
