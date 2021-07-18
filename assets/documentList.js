import Tabulator from 'tabulator-tables';
import 'tabulator-tables/src/scss/bootstrap/tabulator_bootstrap4.scss';

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
      headerSort: false
    },
 	],
});
