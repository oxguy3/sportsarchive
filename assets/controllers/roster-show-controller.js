import { Controller } from "@hotwired/stimulus";
import Dropzone from "dropzone";
import "dropzone/dist/dropzone.css";

Dropzone.autoDiscover = false;

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  initialize() {
    document.getElementById("headshot_role").value = "player";
  }
  connect() {
    let myDropzone = new Dropzone("#headshotDropzone", {
      maxFilesize: 100, // MB
      acceptedFiles: "image/*",
      paramName: "headshot[image]",
    });

    const headshotRoleSelect = document.getElementById("headshotRoleSelect");
    headshotRoleSelect.onchange = function () {
      document.getElementById("headshot_role").value = headshotRoleSelect.value;
    };
  }
}
