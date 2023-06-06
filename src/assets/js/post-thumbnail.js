/**
 * Custom Post Thumbnail
 *
 * @author Takuto Yanagida
 * @version 2023-06-06
 */

function wpinc_post_thumbnail_init(key) {
	const body    = document.querySelector(`.wpinc-dia-post-thumbnail#${key}`);
	const sel_btn = body.getElementsByClassName('select')[0];
	const del_btn = body.getElementsByClassName('delete')[0];
	const tn      = body.getElementsByClassName('thumbnail')[0];

	const media_id = body.querySelector(`[name='${key}']`);

	window.wpinc.dia.setMediaPicker(sel_btn, null, (t, f) => {
		set_item(f);
		del_btn.style.visibility = '';
	}, { multiple: false, type: 'image', title: sel_btn.innerText });

	del_btn.addEventListener('click', e => {
		set_item({ url: '', id: '' });
		del_btn.style.visibility = 'hidden';
	});

	if (media_id.value === '') {
		del_btn.style.visibility = 'hidden';
	}


	// -------------------------------------------------------------------------


	function set_item(f) {
		media_id.value = f.id;

		if (f.url === '') {
			tn.innerHTML = '';
		} else {
			const i = document.createElement('img');
			i.src = f.url;
			tn.appendChild(i);
		}
	}
}
