/**
 * Single Media Picker
 *
 * @author Takuto Yanagida
 * @version 2022-02-02
 */

function wpinc_single_media_picker_init(key) {
	const body    = document.querySelector(`.wpinc-dia-single-media-picker#${key}`);
	const add_row = body.getElementsByClassName('add_row')[0];
	const add_btn = add_row.querySelector('.button.add');

	const item = body.getElementsByClassName('item')[0];

	if (item.querySelector(`[name='${key}[media]']`).value) {
		item.style.display    = '';
		add_row.style.display = 'none';
	} else {
		item.style.display    = 'none';
		add_row.style.display = '';
	}
	assign_event_listener(item);


	// -------------------------------------------------------------------------


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

		del_btn.addEventListener('click', () => {
			set_item(it, { id: '', url: '', title: '', filename: '' });
			it.style.display    = 'none';
			add_row.style.display = '';
		});
		opener.addEventListener('click', () => {
			const url = it.querySelector(`[name='${key}[url]']`).value;
			if (url) window.open(url);
		});
		function on_clicked(t, m) {
			set_item(it, m);
			it.style.display    = '';
			add_row.style.display = 'none';
		}
		setMediaPicker(sel_btn, false, on_clicked, { multiple: false, title: sel_btn.innerText });
		setMediaPicker(add_btn, false, on_clicked, { multiple: false, title: add_btn.innerText });
	}
}
