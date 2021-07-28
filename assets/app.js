// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
// import './bootstrap';

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
fetch('/search/teams.json')
  .then(response => response.json())
  .then(data => autocompleter.setData(data.teams));

import posthog from 'posthog-js'
posthog.init('phc_11Jmnm2KhOP0xeGob7y6k7KbhjcI7bptmXnSK3zVoff', { api_host: 'https://posthog.sportsarchive.net' })
