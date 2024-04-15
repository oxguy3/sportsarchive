import "./bootstrap.js";
// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.scss";

// start the Stimulus application
import "./bootstrap";

import { Dropdown } from "bootstrap";

let navbarSearchInput = document.getElementById("navbarSearchInput");
let navbarSearchMenu = document.getElementById("navbarSearchMenu");
let navbarSearchNoResults = document.getElementById("navbarSearchNoResults");
let dropdown = new Dropdown(navbarSearchMenu);

navbarSearchInput.addEventListener("click", (e) => {
  if (e.target.value.length < 3) {
    dropdown.hide();
  }
});

navbarSearchInput.addEventListener("input", function (e) {
  let query = e.target.value;
  if (query.length < 3) {
    dropdown.hide();
    navbarSearchMenu.replaceChildren(navbarSearchNoResults);
    return;
  }
  fetch(
    "/search/teams.json?" +
      new URLSearchParams({
        q: query,
      })
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.results.length == 0) {
        navbarSearchMenu.replaceChildren(navbarSearchNoResults);
        dropdown.show();
        return;
      }

      let items = [];
      for (const result of data.results) {
        let a = document.createElement("a");
        a.classList.add("dropdown-item");
        a.href = "/" + result.team.type + "/" + result.team.slug;

        let nameSpan = document.createElement("span");
        nameSpan.innerText = result.team.name;
        a.appendChild(nameSpan);

        // don't need a note if there's no TeamNames or if the base name matches
        if (
          result.names.length > 0 &&
          !normalize(result.team.name).includes(normalize(query))
        ) {
          let noteSpan = document.createElement("span");
          noteSpan.classList.add("text-secondary", "small");

          let note = "(";
          let firstName = result.names[0];
          // if there's a primary name, only display that. otherwise list all
          if (result.names[0].type == "primary") {
            note += "known as " + firstName.name;
            if (firstName.firstSeason || firstName.lastSeason) {
              note += ", ";
              note += firstName.firstSeason ? firstName.firstSeason : "";
              note += " – ";
              note += firstName.lastSeason ? firstName.lastSeason : "";
            }
          } else {
            note += "aka " + result.names.map((x) => x.name).join(", ");
          }

          note += ")";
          noteSpan.innerText = note;

          a.appendChild(document.createTextNode(" "));
          a.appendChild(noteSpan);
        }
        items.push(a);
      }
      navbarSearchMenu.replaceChildren(...items);
      dropdown.show();
    });
});
navbarSearchInput.addEventListener("keydown", function (e) {
  // Escape
  if (e.keyCode === 27) {
    dropdown.hide();
    return;
  }
  // ArrowDown
  if (e.keyCode === 40) {
    navbarSearchMenu.children[0]?.focus();
    e.preventDefault();
    return;
  }
});

function normalize(str) {
  // TODO this might have some slight differences from Postgres's UNACCENT() function; might want to change this
  return str
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .trim();
}
