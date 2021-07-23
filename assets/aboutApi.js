import SwaggerUI from 'swagger-ui';
import 'swagger-ui/dist/swagger-ui.css';
const spec = require('./openapi.yaml');

SwaggerUI({
  spec,
  dom_id: '#swagger',
  deepLinking: true,
});
