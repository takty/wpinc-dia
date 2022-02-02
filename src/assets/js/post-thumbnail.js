/**
 * Custom Post Thumbnail
 *
 * @author Takuto Yanagida
 * @version 2022-02-01
 */

function wpinc_dia_post_thumbnail_init(key) {
	const NS = 'wpinc-dia-post-thumbnail';

	const CLS_SEL = NS + '-select';
	const CLS_DEL = NS + '-delete';
	const CLS_IMG = NS + '-img';

	const STR_SEL = document.getElementsByClassName(CLS_SEL)[0].innerText;

	const body = document.getElementById(key);

	const sel = body.getElementsByClassName(CLS_SEL)[0];
	const del = body.getElementsByClassName(CLS_DEL)[0];
	const img = body.getElementsByClassName(CLS_IMG)[0];

	const mid = document.getElementById(`${key}_media`);

	setMediaPicker(sel, false, (target, f) => {
		set_item(f);
		del.style.visibility = '';
	}, { multiple: false, type: 'image', title: STR_SEL });

	del.addEventListener('click', e => {
		set_item({ url: '', id: '' });
		del.style.visibility = 'hidden';
	});

	if (mid.value === '') {
		del.style.visibility = 'hidden';
	}

	function set_item(f) {
		mid.value = f.id;

		if (f.url === '') {
			img.innerHTML = '';
		} else {
			const i = document.createElement('img');
			i.src = f.url;
			img.appendChild(i);
		}
	}
}
