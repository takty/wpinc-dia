/**
 * Link Picker
 *
 * @author Takuto Yanagida
 * @version 2023-03-06
 */

window.wpinc     = window.wpinc ?? {}
window.wpinc.dia = window.wpinc.dia ?? {}

window.wpinc.dia.setLinkPicker = (function () {

	function setLinkPicker(elm, cls = false, fn = null, opts = {}) {
		if (cls === false) cls = 'link';
		opts = Object.assign({ isInternalOnly: false, isLinkTargetAllowed: false, parentGen: -1, postType: null }, opts);

		elm.addEventListener('click', e => {
			if (elm.getAttribute('disabled')) return;
			e.preventDefault();
			createLink(f => {
				const p = (opts.parentGen !== -1) ? getParent(e.target, opts.parentGen) : null;
				if (p) setItem(p, cls, f);
				if (fn) fn(e.target, f);
			}, opts.isInternalOnly, opts.isLinkTargetAllowed, opts.postType);
		});
	}

	function getParent(e, gen) {
		while (0 < gen-- && e.parentNode) e = e.parentNode;
		return e;
	}

	function setItem(p, cls, f) {
		setValueToCls(p, `${cls}-url`, f.url);
		setValueToCls(p, `${cls}-title`, f.title);
		setValueToCls(p, `${cls}-post-id`, '');
	}

	function setValueToCls(p, cls, v) {
		for (const e of Array.from(p.getElementsByClassName(cls))) {
			if (e instanceof HTMLInputElement) {
				e.value = v;
			} else if (e.tagName === 'A') {
				e.setAttribute('href', v);
			} else {
				e.innerText = v;
			}
		}
	}

	function createLink(callbackFunc, isInternalOnly, isLinkTargetAllowed, postType) {
		const id = 'picker-link-ta' + (0 | (Math.random() * 8191));
		const ta = document.createElement('textarea');
		ta.style.display = 'none';
		ta.id = id;
		document.body.appendChild(ta);

		const scan = function () {
			if (wpLink.modalOpen && ta.value === '') return false;

			if (ta.value !== '') {
				const f = readAnchorLink(ta);
				jQuery('#wp-link').find('.query-results').off('river-select', onSelect);
				document.body.removeChild(ta);
				callbackFunc(f);
			}
			postTypeSpec = null;
			wpLink.close();
			return true;
		}
		const onSelect = function (e, li) {
			const val = (li.hasClass('no-title')) ? '' : li.children('.item-title').text();
			jQuery('#wp-link-text').val(val);
		};
		setPostTypeSpecification(postType);

		wpLink.open(id);
		executeTimeoutFunc(scan, 100);
		jQuery('#wp-link').find('.query-results').on('river-select', onSelect);

		jQuery('#link-options').show();
		jQuery('#wplink-enter-url').show();
		jQuery('#wplink-enter-url + div').show();
		jQuery('#wplink-link-existing-content').show();
		jQuery('#link-options > .link-target').show();

		const qrs = document.querySelectorAll('#wp-link .query-results');
		for (let i = 0; i < qrs.length; i += 1) {
			qrs[i].style.maxHeight = 'unset';
		}
		jQuery('.wp-link-text-field').hide();
		let optionHeight = 208;
		if (isInternalOnly) {
			jQuery('#wplink-enter-url').hide();
			jQuery('#wplink-enter-url + div').hide();
			jQuery('#wplink-link-existing-content').hide();
			optionHeight -= 96;
		}
		if (!isLinkTargetAllowed) {
			jQuery('#link-options > .link-target').hide();
			optionHeight -= 32;
		}
		if (isInternalOnly && !isLinkTargetAllowed) {
			jQuery('#link-options').hide();
			optionHeight -= 20;
		}
		for (let i = 0; i < qrs.length; i += 1) {
			qrs[i].style.height = `calc(100% - ${optionHeight}px)`;
		}
	}

	function readAnchorLink(ta) {
		const d = document.createElement('div');
		d.innerHTML = ta.value;
		const a = d.getElementsByTagName('a')[0];
		return { url: a.href, title: a.innerText };
	}

	function executeTimeoutFunc(func, time) {
		const toFunc = function () {
			if (!func()) setTimeout(toFunc, time);
		}
		setTimeout(toFunc, time);
	}

	let postTypeSpec              = null;
	let lastPostTypeSpec          = null;
	let isPostTypeSpecInitialized = false;

	function setPostTypeSpecification(postType) {
		postTypeSpec = postType;
		if (postType === null || postType === lastPostTypeSpec) return;
		lastPostTypeSpec = postType;

		wpLink.init();
		wpLink.lastSearch = '';
		jQuery('#search-results > ul').empty();
		jQuery('#most-recent-results > ul').empty();

		if (isPostTypeSpecInitialized) return;
		isPostTypeSpecInitialized = true;

		jQuery.ajaxSetup({
			beforeSend: function (jqXHR, d) {
				if (!d.data) return true;
				if (!postTypeSpec) return true;
				jQuery.each(d.data.split('&'), function (i, p) {
					const kv = p.split('=');
					if (kv[0] === 'action' && kv[1] === 'wp-link-ajax') {
						d.data += '&link_picker_pt=' + postTypeSpec;
					}
				});
				return true;
			}
		});
	}

	return setLinkPicker;
})();
