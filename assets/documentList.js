import Tabulator from 'tabulator-tables';
import 'tabulator-tables/dist/css/bootstrap/tabulator_bootstrap4.css';

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
  selectable: false, // disables pointer mouse cursor
 	layout: "fitDataFill",
  resizableColumns: "header", // makes columns only resiable by dragging the header (not the cells)
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
  locale: true,
  langs: {
    "en": {
      "ajax": {
        "loading": "Loading", //ajax loader text
        "error": "Error", //ajax error text
      },
      "groups": { //copy for the auto generated item count in group header
        "item": "item", //the singular for item
        "items": "items", //the plural for items
      },
      "pagination": {
        "page_size": "Page size", //label for the page size select element
        "page_title": "Show page",//tooltip text for the numeric page button, appears in front of the page number (eg. "Show Page" will result in a tool tip of "Show Page 1" on the page 1 button)
        "first": "<<", //text for the first page button
        "first_title": "First page", //tooltip text for the first page button
        "last": ">>",
        "last_title": "Last page",
        "prev": "<",
        "prev_title": "Previous page",
        "next": ">",
        "next_title": "Next page",
        "all": "All",
      },
      "headerFilters": {
        "default": "Filter column...", //default header filter placeholder text
        "columns": {
          "team_slug": "Filter teams...",
          "id": "Filter titles...",
          "category": "Filter categories...",
        }
      }
    }
  },
});
table.setLocale("en");
