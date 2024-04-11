import { Controller } from "@hotwired/stimulus";

/** Converts a team name to a slug */
function slugify(str) {
  // to lowercase
  str = str.toLowerCase();
  // remove diacritics, per https://stackoverflow.com/a/37511463
  str = str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  // change all unacceptable characters to dashes
  str = str.replaceAll(/[^a-z0-9]/gi, "-");
  // remove any repeated dashes
  str = str.replaceAll(/-+/g, "-");
  // remove leading/trailing dashes
  str = str.replaceAll(/(^-|-$)/g, "");

  return str;
}

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  initialize() {
    const nameInput = document.getElementById("team_name");
    const slugInput = document.getElementById("team_slug");
    let shouldSlugify = true;

    function updateSlug() {
      if (shouldSlugify) {
        slugInput.value = slugify(nameInput.value);
      }
    }
    nameInput.addEventListener("input", updateSlug);
    slugInput.addEventListener("input", function (e) {
      if (slugInput.value != "") {
        shouldSlugify = false;
      } else {
        shouldSlugify = true;
      }
    });
    slugInput.addEventListener("blur", updateSlug);
  }
}
