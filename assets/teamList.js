function handleFilterClick(button, filterType) {
  // highlight only this button
  document.querySelectorAll('.filter-'+filterType).forEach(
    btn => btn.classList.remove("active")
  );
  button.classList.add("active");

  // update show classes on teams
  let teams = document.querySelectorAll('.team');
  if (button.hasAttribute('data-filter')) {
    const filter = button.getAttribute('data-filter');
    teams.forEach(team => team.classList.remove('show-'+filterType));
    let selectedTeams = [];

    // if the filter selection was the "other" button
    if (filter == '*') {
      // get the list of all the sports/whatevers that all the other buttons cover
      let filterBtns = document.querySelectorAll('.filter-'+filterType+'[data-filter]');
      let filterValues = [];
      filterBtns.forEach(function(btn) {
        const filterValue = btn.getAttribute('data-filter');
        if (filterValue != '*') {
          filterValues.push(filterValue);
        }
      });

      // filter the entire teams list down to the 'other's
      teams.forEach(function(team) {
        const teamValue = team.getAttribute('data-'+filterType);
        if (!filterValues.includes(teamValue)) {
          selectedTeams.push(team);
        }
      });

    } else {
      selectedTeams = document.querySelectorAll('.team[data-'+filterType+'='+filter+']')
    }
    selectedTeams.forEach(team => team.classList.add('show-'+filterType))
  } else {
    teams.forEach(team => team.classList.add('show-'+filterType));
  }

  // update team count
  var count = document.querySelectorAll('.team.show-sport.show-country.show-gender.show-active').length;
  document.getElementById('teamCount').innerText = count;

  // show message if no teams visible
  const noTeams = document.getElementById('noTeams');
  if (count == 0) {
    noTeams.classList.remove('d-none');
  } else {
    noTeams.classList.add('d-none');
  }
}

const searchParams = new URLSearchParams(window.location.search);
const filterTypes = ['sport', 'country', 'gender', 'active'];
filterTypes.forEach(function(filterType) {

  // enable button listener
  var buttons = document.querySelectorAll('.filter-'+filterType)
  buttons.forEach(function(button) {
    button.onclick = function() {
      handleFilterClick(this, filterType);
    };
    button.removeAttribute("disabled");
  });

  // check the URL search params for filters
  if (searchParams.has(filterType)) {
    const filterVal = searchParams.get(filterType);
    document.querySelector('.filter-'+filterType+'[data-filter='+filterVal+']').click();
  }
});
