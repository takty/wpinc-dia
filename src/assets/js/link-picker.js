/**
 * Link Picker
 *
 * @author Takuto Yanagida
 * @version 2023-02-10
 */

function wpinc_link_picker_init(key, internal_only = false, max_count = null, do_allow_url_hash = false, post_type = null) {
	const body    = document.querySelector(`.wpinc-dia-link-picker#${key}`);
	const add_row = body.getElementsByClassName('add-row')[0];
	const add_btn = add_row.querySelector('.button.add');

	const tbl   = body.getElementsByClassName('table')[0];
	const items = tbl.getElementsByClassName('item');
	const temp  = tbl.querySelector('.item-template');

	const picker_opts = {
		isInternalOnly     : internal_only,
		isLinkTargetAllowed: false,
		postType           : post_type
	};

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
	if (max_count && max_count <= items.length) add_btn.setAttribute('disabled', 'true');

	window.wpinc.dia.setLinkPicker(add_btn, false, (t, l) => {
		add_new_item(l);
		reorder_item_names();
		if (max_count && max_count <= items.length) add_btn.setAttribute('disabled', 'true');
	}, Object.assign(picker_opts, { title: add_btn.innerText }));


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

	function add_new_item(l) {
		const it = temp.cloneNode(true);
		set_item(it, l);
		it.classList.remove('item-template');
		it.classList.add('item');
		tbl.insertBefore(it, add_row);
		assign_event_listener(it);
	}

	function set_item(it, l) {
		it.querySelector('*[data-key="url"]').value     = l.url;
		it.querySelector('*[data-key="title"]').value   = l.title;
		it.querySelector('*[data-key="post_id"]').value = '';
		if (internal_only && !do_allow_url_hash) {
			it.querySelector('*[data-key="url"]').readOnly = true;
		}
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
			const url = it.querySelector('*[data-key="url"]').value;
			if (url) window.open(url);
		});
		window.wpinc.dia.setLinkPicker(sel_btn, false, (t, l) => set_item(it, l), Object.assign(picker_opts, { title: sel_btn.innerText }));
	}
}
