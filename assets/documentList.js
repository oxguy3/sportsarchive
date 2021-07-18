import Tabulator from 'tabulator-tables';
import 'tabulator-tables/src/scss/bootstrap/tabulator_bootstrap4.scss';

/** Capitalizes the first letter of a string */
function ucfirst(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
/** Converts a slug to a user-friendly display string */
function unslug(string) {
  return ucfirst(string.replaceAll('-', ' '));
}

Tabulator.prototype.extendModule("format", "formatters", {
    unslug: function(cell, formatterParams){
        return unslug(cell.getValue());
    },
});

let table = new Tabulator("#documentsTable", {
  pagination: "remote",
  ajaxURL: "/documents.json",
  // ajaxSorting: true,
  ajaxFiltering: true,
  paginationSize: 10,
  paginationSizeSelector: [10, 25, 50, 100],
 	layout: "fitColumns",
 	columns: [
    {
      title: "Team",
      field: "team_slug",
      formatter: "link",
      formatterParams:{
        labelField: "team_name",
        urlPrefix: "/teams/"
      },
      headerFilter: true,
      headerSort: false
    },
    {
      title: "Title",
      field: "id",
      formatter: "link",
      formatterParams: {
        labelField: "title",
        urlPrefix: "/documents/"
      },
      headerFilter: true,
      headerSort: false
    },
	 	{
      title: "Category",
      field: "category",
      formatter: "unslug",
      headerFilter: "select",
      headerFilterParams: {
          values: ["", "unsorted", "branding", "directories", "game-notes", "legal-documents", "media-guides", "miscellany", "programs", "rosters", "rule-books", "schedules", "season-reviews"],
          listItemFormatter: function(value, title){
              return unslug(title);
          },
      },
      headerSort: false
    },
 	],
});
