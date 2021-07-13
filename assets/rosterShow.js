import $ from 'jquery';
import Dropzone from "dropzone";
import "dropzone/dist/dropzone.css";

Dropzone.options.headshotDropzone = {
  maxFilesize: 50, // MB
  acceptedFiles: "image/*"
};

$("#headshotRoleSelect").change(function() {
  $("#headshotRoleHiddenInput").val($(this).val());
});
