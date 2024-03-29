import './styles/teamList.scss';

/** https://stackoverflow.com/a/9899701 */
function docReady(fn) {
    // see if DOM is already available
    if (document.readyState === "complete" || document.readyState === "interactive") {
        // call on next available tick
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

const jsData = document.getElementById('jsData');
const countFilter = parseInt(jsData.getAttribute('data-count-filter'));
const pageSize = parseInt(jsData.getAttribute('data-page-size'));
let pageNum = parseInt(jsData.getAttribute('data-page-num'));
const pageCount = Math.ceil(countFilter/pageSize);
let isError = false;

// don't need the pagination buttons if JS is working
document.getElementById('pagination').classList.add('d-none');

window.addEventListener('scroll', tryToLoadMorePages, { passive: true });
window.addEventListener('resize', tryToLoadMorePages, { passive: true });
document.addEventListener('touchmove', tryToLoadMorePages, { passive: true });
docReady(tryToLoadMorePages);

let isAwaitingNewPage = false;

async function tryToLoadMorePages() {
  const isScrolledDown = window.scrollY + window.innerHeight + 100 >= document.documentElement.scrollHeight;
  if (isScrolledDown && pageNum < pageCount && !isError && !isAwaitingNewPage) {
    loadMorePages();
  }
}

function loadMorePages() {
  isAwaitingNewPage = true;
  let loadingMore = document.getElementById('loadingMore');
  loadingMore.classList.remove('d-none');

  let searchParams = new URLSearchParams(window.location.search);
  searchParams.set('raw', 'true');
  searchParams.set('page', ++pageNum);

  let url = window.location.pathname + '?' + searchParams.toString();
  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error(response.status);
      }
      return response.text();
    })
    .then(function(data) {
      let teamsNode = document.getElementById('teams');
      teamsNode.innerHTML += data;

      loadingMore.classList.add('d-none');
      if (pageNum >= pageCount) {
        showReachedEnd();
      } else {
        isAwaitingNewPage = false;
        setTimeout(tryToLoadMorePages, 1);
      }
    })
    .catch(error => {
      isError = true;
      console.error('Loading more pages failed:', error);
      document.getElementById('loadingError').classList.remove('d-none');
      loadingMore.classList.add('d-none');
    });
}

function showReachedEnd() {
  document.getElementById('reachedEnd').classList.remove('d-none');
}
