import Dropzone from "dropzone";
import "dropzone/dist/dropzone.css";

Dropzone.options.headshotDropzone = {
  maxFilesize: 50, // MB
  acceptedFiles: "image/*"
};

const headshotRoleSelect = document.getElementById('headshotRoleSelect');
headshotRoleSelect.onchange = function() {
  document.getElementById("headshotRoleHiddenInput").value = headshotRoleSelect.value;
};
