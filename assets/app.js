// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
// import './bootstrap';

import $ from 'jquery';
import bootstrap from 'bootstrap';
import Autocomplete from './autocomplete';

var autocompleter = new Autocomplete(document.getElementById('mainSearch'), {
    data: [],
    label: "name",
    value: "slug",
    maximumItems: 8,
    onSelectItem: function(item) {
        window.location.href = "/teams/" + item.value;
    }
});
$.ajax('/teams.json', {
  success: function(data) {
    autocompleter.setData(data.teams);
  }
});
