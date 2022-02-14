let next_row_id = document.getElementById("next_row_id");
let articles = document.getElementById("article_list");
let stores = document.getElementById("store_list")
let button_add;

const handleEvents = function(e) {
	if (e.target.id == "button_add") add_row(e);
	else if (e.target.classList.contains("store__list__item") || e.target.classList.contains("article__list__item")) setValue(e);
	else if (e.target.classList.contains("input__store") || e.target.classList.contains("input__article") || e.target.classList.contains("input__aantal") || e.target.classList.contains("input__prijs")) handleInput(e);
	else if (e.target.classList.contains("button__detail")) redirect(e);
	else if (e.target.classList.contains("button__delete__new")) removeRow(e);
	else if (e.target.classList.contains("button__edit")) edit(e);
}

const add_row = function(e) {
	let parent = e.target.parentElement;
	e.target.previousElementSibling.classList.remove("form-row--hidden");
	fetch("./templates/boodschap_detail_add_row.html")
		.then(response => response.text())
		.then((new_row) => {
			setNamesNewRow(e.target.previousElementSibling);
			next_row_id.setAttribute("value", parseInt(next_row_id.value) + 1);
			parent.innerHTML += new_row.replace(/@next_row_id@/g, parseInt(next_row_id.value));
			parent.removeChild(document.getElementById("button_add"));
		});
}

const setNamesNewRow = function(el) {
	let nri = parseInt(next_row_id.value);
	el.children[0].children[0].setAttribute("name", `data[${nri}][row_sto_id]`);
	el.children[0].children[1].setAttribute("name", `data[${nri}][sto_name]`);
	el.children[1].children[0].setAttribute("name", `data[${nri}][row_art_id]`);
	el.children[1].children[1].setAttribute("name", `data[${nri}][art_name]`);
	el.children[2].children[0].setAttribute("name", `data[${nri}][row_pieces]`);
	el.children[3].children[0].setAttribute("name", `data[${nri}][pri_value]`);
	el.children[4].children[1].setAttribute("value", `delete-${nri}`);

}

const handleInput = function(e) {
	if (Object.values(e.target.attributes).filter(el => el.value == "readonly").length == 0) {
		e.target.addEventListener("input", updateValue);
		e.target.addEventListener("focus", setFocus);
		e.target.addEventListener("blur", looseFocus);
	}
}

const setFocus = function(e) {
	const options_list = e.target.parentElement.children[2];
	const class_list = e.target.classList.value;
	const list = class_list.includes("input__article") ? articles : class_list.includes("input__store") ? stores : null;

	if ((e.target.value.length > 0) && (e.target.classList.value.includes("input__store") || e.target.classList.value.includes("input__article"))) {
		options_list.style.display = "block";
		options_list_items = filterOptions(e, list);
		options_list.innerHTML = options_list_items.reduce((str, el) => str + el.outerHTML, "");
	} else if (e.target.value.length == 0) {
		options_list.innerHTML = "";
		options_list.classList.add("list--hidden");
		console.log("net voor de unsetSelect");
		unsetSelect(e);
	}
}

const filterOptions = function(e, list) {
	const form = document.getElementById("form");
	if (form.value == "artikel-detail") {
		return Array(...list.children).filter(el => el.outerText.toLowerCase().includes(e.target.value.toLowerCase())).slice(0, 10);
	}

	const sto_id = e.target.classList.value.includes("input__store") ? e.target.previousElementSibling.children[0].value : e.target.parentElement.parentElement.children[0].children[0].children[0].value;
	const art_id = e.target.classList.value.includes("input__article") ? e.target.previousElementSibling.children[0].value : e.target.parentElement.parentElement.children[1].children[0].children[0].value;

	//indien gebruiker zoekt op artikel, toon enkel artikelen die verkrijgbaar is in de geselecteerde winkel.
	//geen winkel geselecteerd? toon dan ieder artikel, maar slechts 1 maal.
	if (e.target.classList.value.includes("input__article")) {
		return Array(...list.children).filter(el => el.outerText.toLowerCase().includes(e.target.value.toLowerCase()))
			.filter(el => {
				const el_sto_id = el.classList[2].split("sto_id-")[1];
				return sto_id ? el_sto_id == sto_id : true;
			})
			.filter((el, i, list) => {
				return !list.slice(0, i).map(el => el.id).includes(el.id);
			});
	}

	//indien gebruiker zoekt op winkel, toon enkel winkels die het geslecteerde artikel verkopen.
	// geen winkel geselecteerd? toon dan iedere winkel, maar slechts 1 maal.
	if (e.target.classList.value.includes("input__store")) {
		return Array(...list.children).filter(el => el.outerText.toLowerCase().includes(e.target.value.toLowerCase()))
			.filter(el => {
				const el_art_id = el.classList[1].split("art_id-")[1];
				return art_id ? el_art_id == art_id : true;
			})
			.filter((el, i, list) => {
				return !list.slice(0, i).map(el => el.id).includes(el.id);
			});
	}


	return Array(...list.children).filter(el => el.outerText.toLowerCase().includes(e.target.value.toLowerCase())).slice(0, 10);
}

const updateValue = function(e) {
	e.target.setAttribute("value", e.target.value);
	setFocus(e);
}

const removeListItems = function(e) {
	e.target.nextElementSibling.style.display = "none";
}

const looseFocus = function(e) {
	setTimeout(removeListItems, 300, e);
}

const setValue = function(e) {
	if (e.target.classList.contains("store__list__item") || e.target.classList.contains("article__list__item")) {
		const form = document.getElementById("form");
		const input_field = e.target.parentElement.previousElementSibling;
		const select_field = e.target.parentElement.parentElement.children[0].children[0];

		input_field.setAttribute("value", e.target.innerText);
		input_field.value = e.target.innerText;
		select_field.value = parseInt(e.target.id);
		if (form.value == "boodschapdetail" && e.target.parentElement.classList.contains("article_list")) {
			const detail_button = e.target.parentElement.parentElement.parentElement.children[4].children[0].children[0];
			detail_button.attributes[3].value += e.target.id;
			//console.log(e.target.classList)
			detail_button.removeAttribute("disabled");
		}
	}
}

const unsetSelect = function(e) {
	e.target.previousElementSibling.children[0].setAttribute("value", "");
}

const redirect = function(e) {
	const refer = document.getElementById("refer");
	refer.setAttribute("value", e.target.attributes[3].value);

}

const removeRow = function(e) {
	const rows_ul = e.target.parentElement.parentElement.parentElement;
	const this_row = e.target.parentElement.parentElement;
	rows_ul.removeChild(this_row);
}

const edit = function(e) {
	const this_row = e.target.parentElement.parentElement;
	const children = [...this_row.children];
	children[0].children[1].toggleAttribute("readonly");
	children[1].children[1].toggleAttribute("readonly");
	children[2].children[0].toggleAttribute("readonly");
	children[3].children[0].toggleAttribute("readonly");
}

document.addEventListener("click", handleEvents);