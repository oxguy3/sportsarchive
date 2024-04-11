import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  initialize() {
    //asdf
  }
  cancel() {
    window.history.back();
  }
}
