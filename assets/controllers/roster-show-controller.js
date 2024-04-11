import { Controller } from "@hotwired/stimulus";
import Dropzone from "dropzone";
import "dropzone/dist/dropzone.css";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  initialize() {
    Dropzone.options.headshotDropzone = {
      maxFilesize: 100, // MB
      acceptedFiles: "image/*",
    };

    const headshotRoleSelect = document.getElementById("headshotRoleSelect");
    headshotRoleSelect.onchange = function () {
      document.getElementById("headshotRoleHiddenInput").value =
        headshotRoleSelect.value;
    };
  }
}
