import $ from 'jquery';

function handleFilterClick(button, filterType) {
  // highlight only this button
  $('.filter-'+filterType).removeClass("active");
  $(button).addClass("active");

  // update show classes on teams
  var filter = $(button).attr('data-filter');
  if (typeof filter === 'undefined') {
    $('.team').addClass('show-'+filterType);
  } else {
    $('.team').removeClass('show-'+filterType);
    $('.team[data-'+filterType+'='+filter+']').addClass('show-'+filterType);
  }

  // update team count
  var count = $('.team.show-sport.show-country.show-gender.show-active').length;
  $('#teamCount').text(count);
  if (count == 0) {
    $('#noTeams').removeClass('d-none');
  } else {
    $('#noTeams').addClass('d-none');
  }
}

var searchParams = new URLSearchParams(window.location.search);
var filterTypes = ['sport', 'country', 'gender', 'active'];
filterTypes.forEach(function(filterType) {

  // enable button listener
  $('.filter-'+filterType).click(function() {
    handleFilterClick(this, filterType);
  });
  $('.filter-'+filterType).prop("disabled", false);

  // check the URL search params for filters
  if (searchParams.has(filterType)) {
    var filterVal = searchParams.get(filterType);
    $('.filter-'+filterType+'[data-filter='+filterVal+']').click();
  }
});
