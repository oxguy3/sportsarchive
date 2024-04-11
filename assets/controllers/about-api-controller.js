import { Controller } from "@hotwired/stimulus";
import SwaggerUI from "swagger-ui";
import "swagger-ui/dist/swagger-ui.css";
const spec = require("../openapi.yaml");

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  initialize() {
    SwaggerUI({
      spec,
      dom_id: "#swagger",
      deepLinking: true,
    });
  }
}
