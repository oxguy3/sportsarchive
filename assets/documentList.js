import Tabulator from 'tabulator-tables';
import 'tabulator-tables/src/scss/bootstrap/tabulator_bootstrap4.scss';

/** Capitalizes the first letter of a string */
function ucfirst(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

Tabulator.prototype.extendModule("format", "formatters", {
    unslug: function(cell, formatterParams){
        return ucfirst(cell.getValue().replaceAll('-', ' '));
    },
});

let table = new Tabulator("#documentsTable", {
  pagination: "remote",
  ajaxURL: "/documents.json",
  ajaxSorting: true,
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
      headerSort: false
    },
	 	{
      title: "Category",
      field: "category",
      formatter: "unslug",
      headerSort: false
    },
 	],
});
