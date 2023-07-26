/**
 * Media Picker
 *
 * @author Takuto Yanagida
 * @version 2023-07-26
 */

function wpinc_media_picker_init(key) {
	const body    = document.querySelector(`.wpinc-dia-media-picker#${key}`);
	const add_row = body.getElementsByClassName('add-row')[0];
	const add_btn = add_row.querySelector('.button.add');

	const tbl   = body.getElementsByClassName('table')[0];
	const items = tbl.getElementsByClassName('item');
	const temp  = tbl.querySelector('.item-template');

	jQuery(tbl).sortable();
	jQuery(tbl).sortable('option', {
		axis       : 'y',
		containment: 'parent',
		cursor     : 'move',
		handle     : '.handle',
		items      : '> .item',
		placeholder: 'item-placeholder',
		update     : reorder_item_names,
	});

	reorder_item_names();
	for (const it of items) assign_event_listener(it);
	window.wpinc.dia.setMediaPicker(add_btn, null, (t, fs) => {
		for (const f of fs) add_new_item(f);
		reorder_item_names();
	}, { multiple: true, title: add_btn.innerText });


	// -------------------------------------------------------------------------


	function reorder_item_names() {
		for (let i = 0; i < items.length; i += 1) {
			const inputs = items[i].querySelectorAll('*[data-key]');
			for (const input of inputs) {
				const sub  = input.dataset.key;
				input.name = `${key}[${i}][${sub}]`;
			}
		}
	}

	function add_new_item(f) {
		const it = temp.cloneNode(true);
		set_item(it, f);
		it.classList.remove('item-template');
		it.classList.add('item');
		tbl.insertBefore(it, add_row);
		assign_event_listener(it);
	}

	function set_item(it, f) {
		it.querySelector(`*[data-key='media_id']`).value   = f.id;
		it.querySelector(`*[data-key='url']`).value        = f.url;
		it.querySelector(`*[data-key='title']`).value      = f.title;
		it.querySelector(`*[data-key='filename']`).value   = f.filename;
		it.getElementsByClassName('filename')[0].innerText = f.filename;
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
			const url = it.querySelector(`*[data-key='url']`).value;
			if (url) window.open(url);
		});
		window.wpinc.dia.setMediaPicker(sel_btn, null, (t, f) => set_item(it, f), { multiple: false, title: sel_btn.innerText });
	}
}
