import { Controller } from "@hotwired/stimulus";

/** https://stackoverflow.com/a/9899701 */
function docReady(fn) {
  // see if DOM is already available
  if (
    document.readyState === "complete" ||
    document.readyState === "interactive"
  ) {
    // call on next available tick
    setTimeout(fn, 1);
  } else {
    document.addEventListener("DOMContentLoaded", fn);
  }
}

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    countFilter: Number,
    pageSize: Number,
    pageNum: Number,
  };
  isAwaitingNewPage = false;
  isError = false;
  pageNum = 0;
  pageCount = 0;

  tryToLoadMorePages() {
    const isScrolledDown =
      window.scrollY + window.innerHeight + 100 >=
      document.documentElement.scrollHeight;
    if (
      isScrolledDown &&
      this.pageNum < this.pageCount &&
      !this.isError &&
      !this.isAwaitingNewPage
    ) {
      this.loadMorePages();
    }
  }

  loadMorePages() {
    this.isAwaitingNewPage = true;
    let loadingMore = document.getElementById("loadingMore");
    loadingMore.classList.remove("d-none");

    let searchParams = new URLSearchParams(window.location.search);
    searchParams.set("raw", "true");
    searchParams.set("page", ++this.pageNum);

    let url = window.location.pathname + "?" + searchParams.toString();
    fetch(url)
      .then((response) => {
        if (!response.ok) {
          throw new Error(response.status);
        }
        return response.text();
      })
      .then(
        function (data) {
          let teamsNode = document.getElementById("teams");
          teamsNode.innerHTML += data;

          loadingMore.classList.add("d-none");
          if (this.pageNum >= this.pageCount) {
            this.showReachedEnd();
          } else {
            this.isAwaitingNewPage = false;
            setTimeout(this.tryToLoadMorePages.bind(this), 1);
          }
        }.bind(this)
      )
      .catch((error) => {
        this.isError = true;
        console.error("Loading more pages failed:", error);
        document.getElementById("loadingError").classList.remove("d-none");
        loadingMore.classList.add("d-none");
      });
  }

  showReachedEnd() {
    document.getElementById("reachedEnd").classList.remove("d-none");
  }

  connect() {
    this.pageNum = this.pageNumValue;
    this.pageCount = Math.ceil(this.countFilterValue / this.pageSizeValue);

    // don't need the pagination buttons if JS is working
    document.getElementById("pagination").classList.add("d-none");

    window.addEventListener("scroll", this.tryToLoadMorePages.bind(this), {
      passive: true,
    });
    window.addEventListener("resize", this.tryToLoadMorePages.bind(this), {
      passive: true,
    });
    document.addEventListener("touchmove", this.tryToLoadMorePages.bind(this), {
      passive: true,
    });
    this.tryToLoadMorePages();
  }
}
