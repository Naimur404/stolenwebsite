class ScrollBarHelper {
  constructor() {
      this._element = document.body;
  }

  getWidth() {
      // https://developer.mozilla.org/en-US/docs/Web/API/Window/innerWidth#usage_notes
      const documentWidth = document.documentElement.clientWidth;
      return Math.abs(window.innerWidth - documentWidth);
  }

  hide() {
      const width = this.getWidth();
      this._disableOverFlow(); // give padding to element to balance the hidden scrollbar width
      this._setElementAttributes(
          this._element,
          'paddingRight',
          (calculatedValue) => calculatedValue + width
      ); // trick: We adjust positive paddingRight and negative marginRight to sticky-top elements to keep showing fullwidth
  }

  _disableOverFlow() {
      this._saveInitialAttribute(this._element, 'overflow');
      this._element.style.overflow = 'hidden';
  }

  _setElementAttributes(selector, styleProp, callback) {
      const scrollbarWidth = this.getWidth();
      const manipulationCallBack = (element) => {
          if (
              element !== this._element &&
              window.innerWidth > element.clientWidth + scrollbarWidth
          ) {
              return;
          }

          this._saveInitialAttribute(element, styleProp);

          const calculatedValue = window.getComputedStyle(element)[styleProp];
          element.style[styleProp] = `${callback(
              Number.parseFloat(calculatedValue)
          )}px`;
      };

      this._applyManipulationCallback(selector, manipulationCallBack);
  }

  reset() {
      this._resetElementAttributes(this._element, 'overflow');
      this._resetElementAttributes(this._element, 'paddingRight');
  }

  _saveInitialAttribute(element, styleProp) {
      const actualValue = element.style[styleProp];
      if (actualValue) {
          $(element).data('bs-' + styleProp, actualValue);
      }
  }

  _resetElementAttributes(selector, styleProp) {
      const manipulationCallBack = (element) => {
          const value = $(element).data('bs-' + styleProp);

          if (typeof value === 'undefined') {
              element.style.removeProperty(styleProp);
          } else {
              $(element).removeData('bs-' + styleProp);
              element.style[styleProp] = value;
          }
      };

      this._applyManipulationCallback(selector, manipulationCallBack);
  }

  getWindow(node) {
      if (node == null) {
          return window;
      }

      if (node.toString() !== '[object Window]') {
          var ownerDocument = node.ownerDocument;
          return ownerDocument ? ownerDocument.defaultView || window : window;
      }

      return node;
  }

  isElement(node) {
      var OwnElement = this.getWindow(node).Element;
      return node instanceof OwnElement || node instanceof Element;
  }

  _applyManipulationCallback(selector, callBack) {
      if (this.isElement(selector)) {
          callBack(selector);
      } else {
          []
              .concat(
                  ...Element.prototype.querySelectorAll.call(
                      this._element,
                      selector
                  )
              )
              .forEach(callBack);
          //  $(this._element).find(selector).each(callBack);
      }
  }

  isOverflowing() {
      return this.getWidth() > 0;
  }
}
