let next_row_id = parseInt(document.getElementById("next_row_id").value);
let articles = document.getElementById("article_list");
let stores = document.getElementById("store_list")
let button_add;

//let input__stores;

const handleEvents = function(e) {
	if (e.target.id == "button_add") add_row(e);
	else if (e.target.classList.contains("store__list__item") || e.target.classList.contains("article__list__item")) setValue(e);
	else if (e.target.classList.contains("input__store") || e.target.classList.contains("input__article") || e.target.classList.contains("input__aantal") || e.target.classList.contains("input__prijs")) handleInput(e);
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
		e.target.addEventListener("focus", setFocus);
		e.target.addEventListener("blur", looseFocus, 100);
	}
}

/*if (this.classList.value.includes("input__article") || this.classList.value.includes("input__store")) {
				//console.log("leaving input__article or input__store");
				this.nextElementSibling.classList.add("list--hidden");
				//console.log(e.target);
				//console.log(this);
				if (!e.target.classList.contains("store__list__item") && !e.target.classList.contains("article__list__item")) {
					this.nextElementSibling.innerHTML = "";
				}
			}
		}
	)
}
}
}*/

const setFocus = function(e) {
	const options_list = e.target.parentElement.children[2];
	const class_list = e.target.classList.value;
	const list = class_list.includes("input__article") ? articles : class_list.includes("input__store") ? stores : null;
	if ((e.target.value.length > 0) && (e.target.classList.value.includes("input__store") || e.target.classList.value.includes("input__article"))) {
		options_list.style.display = "block";
		options_list_items = Array(...list.children).filter(el => el.outerText.toLowerCase().includes(e.target.value.toLowerCase())).slice(0, 10);
		options_list_items.forEach(el => el.onclick = setValue);
		options_list.innerHTML = options_list_items.reduce((str, el) => str + el.outerHTML, "");
	} else if (e.target.value.length == 0) {
		options_list.innerHTML = "";
		options_list.classList.add("list--hidden");
	}
}

const updateValue = function(e) {
	e.target.setAttribute("value", e.target.value);
	setFocus(e);
}

const removeListItems = function(e) {
	e.target.nextElementSibling.style.display = "none";
}

const looseFocus = function(e) {
	setTimeout(removeListItems, 100, e);
}

const setValue = function(e) {
	if (e.target.classList.contains("store__list__item") || e.target.classList.contains("sotre__list__item")) {
		const detail_button = e.target.parentElement.parentElement.parentElement.children[4].children[0];
		const input_field = e.target.parentElement.previousElementSibling;
		const select_field = e.target.parentElement.parentElement.children[0].children[0];

		input_field.setAttribute("value", e.target.innerHTML);
		input_field.value = e.target.innerText;
		console.log(e);
		select_field.value = e.target.id;
		if (e.target.parentElement.classList.contains("article_list")) {
			detail_button.attributes[3].value += e.target.id;
			detail_button.removeAttribute("disabled");
		}
	}
}

const redirect = function(e) {
	e.target.parentElement.parentElement.parentElement.parentElement.children[2].setAttribute("value", e.target.attributes[3].value);

}

document.addEventListener("click", handleEvents);