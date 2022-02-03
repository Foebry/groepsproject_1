let next_row_id = parseInt(document.getElementById("next_row_id").value);
let articles = document.getElementById("article_list");
let stores = document.getElementById("store_list")
let button_add;

//let input__stores;

const handleEvents = function(e) {
	if (e.target.id == "button_add") add_row(e);
	else if (e.target.classList.contains("input__store") || e.target.classList.contains("input__article") || e.target.classList.contains("input__aantal") || e.target.classList.contains("input__prijs")) handleInput(e);
	else if (e.target.classList.contains("store__list__item") || e.target.classList.contains("article__list__item")) setValue(e);
	else if (e.target.classList.contains("button__detail")) redirect(e);
}

const add_row = function(e) {
	let parent = e.target.parentElement;
	e.target.previousElementSibling.classList.remove("form-row--hidden");
	fetch("../templates/boodschap_detail_add_row.html")
		.then(response => response.text())
		.then((new_row) => {
			next_row_id += 1;
			parent.innerHTML += new_row.replace(/@next_row_id@/g, next_row_id.toString());
			parent.removeChild(document.getElementById("button_add"));
		});
}

const handleInput = function(e) {
	if (Object.values(e.target.attributes).filter(el => el.value == "readonly").length == 0) {
		e.target.addEventListener("input", updateValue);
		e.target.addEventListener("blur", looseFocus);
		e.target.addEventListener("focus", updateValue);
	}
}

const updateValue = function() {
	if (this.value.length > 0 && (this.classList.value.includes("input__article") || this.classList.value.includes("input__store"))) {
		const options_list = this.parentElement.children[2];
		const class_list = this.classList.value;
		const list = class_list.includes("input__article") ? articles : class_list.includes("input__store") ? stores : null;
		options_list.classList.remove("list--hidden");
		options_list.innerHTML = Array(...list.children).filter(el => el.outerText.toLowerCase().includes(this.value.toLowerCase())).slice(0, 10).reduce((str, el) => str + el.outerHTML, "");
	}
	this.setAttribute("value", this.value);
}

const looseFocus = function() {
	if (this.classList.value.includes("input__article") || this.classList.value.includes("input__store")) {
		this.nextElementSibling.classList.add("list--hidden");
	}
}

const setValue = function(e) {
	const detail_button = e.target.parentElement.parentElement.parentElement.children[4].children[0];
	//console.log(detail_button.attributes[3].value);
	const input_field = e.target.parentElement.previousElementSibling;
	const select_field = e.target.parentElement.parentElement.children[0].children[0];

	input_field.setAttribute("value", e.target.innerHTML);
	input_field.value = e.target.innerHTML;
	select_field.value = e.target.id;
	if (e.target.parentElement.classList.contains("article_list")) {
		detail_button.attributes[3].value += e.target.id;
		detail_button.removeAttribute("disabled");
	}
	e.target.parentElement.innerHTML = "";
	console.log(detail_button);
}

const redirect = function(e) {
	e.target.parentElement.parentElement.parentElement.parentElement.children[2].setAttribute("value", e.target.attributes[3].value);

}

document.addEventListener("click", handleEvents);