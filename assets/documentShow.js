// import { IaBookReader } from '@internetarchive/bookreader';

// import '@internetarchive/bookreader/src/BookReader';

const jsData = document.getElementById('jsData');
const documentFileUrl = jsData.getAttribute('data-file-url');
const documentId = jsData.getAttribute('data-document-id');

fetch('/documents/'+documentId+'/pages.json')
  .then(response => response.json())
  .then(function(pagesData) {

    // reformat page metadata into BookReader's schema
    let brData = [];
    for (const page of pagesData) {
        brData.push({
            width: page.w,
            height: page.h,
            uri: documentFileUrl+'_page'+page.pg+'.png'
        });
    }

    var options = {
        data: [
            brData
        ],

        // bookTitle: 'Simple BookReader Presentation',

        // thumbnail is optional, but it is used in the info dialog
        thumbnail: brData[0].uri,

        // Metadata is optional, but it is used in the info dialog
        // metadata: [
        //     {label: 'Title', value: 'Open Library BookReader Presentation'},
        //     {label: 'Author', value: 'Internet Archive'},
        //     {label: 'Demo Info', value: 'This demo shows how one could use BookReader with their own content.'},
        // ],

        ui: 'full', // embed, full (responsive)

        imagesBaseURL: '/build/BookReader/images/',

    };
    let br = new BookReader(options);

    console.log(br);
    // Let's go!
    br.init();
  });

