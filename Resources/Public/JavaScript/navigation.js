/* Navigation, search, lang menu handeling */
(function() {
	const debounce = (func, delay) => {
		if (!delay) {
			delay = 100;
		}

		var timer;
		return (event) => {
			if(timer) clearTimeout(timer);
			timer = setTimeout(func, delay, event);
		};
	}

	function HeaderNavigation(navigationContainer) {

		this.container = navigationContainer;
		this.navigationEl = this.container.querySelector('nav');
		this.navigation = this.container.querySelector('ul');
		this.isFullPageNav = false;

		// standard single container navigation detected
		if(this.container.id === "main-navigation-container") {
			this.button = document.getElementById('main-navigation-toggle');

			// Hide navigation toggle button if navigation is empty and return early.
			if (!this.navigation ) {
				console.log('this navigation is empty:', this.container);
				this.button.style.display = 'none';
				return;
			}

			this.lastButtonState = null;
			this.hamburgerMenuWasSetup = false;
			this.subMenuListenersSet = false;

			this.chooseTheMainNavigationSetup();

			// search field
			this.searchToggleEl = document.getElementById('main-search-toggle');
			if (this.searchToggleEl) {
				this.setupSearchField();
			}

		// Fullpage navigation detected
		} else if(this.container.id === "full-page-navigation") {
			this.button = document.getElementById('main-navigation-toggle');
			this.isFullPageNav = true;

			// Hide navigation toggle button if navigation is empty and return early.
			if (!this.navigation ) {
				console.log('this navigation is empty:', this.container);
				this.button.style.display = 'none';
				return;
			}

			this.lastButtonState = 'flex';
			this.hamburgerMenuWasSetup = false;
			this.subMenuListenersSet = false;

			this.setupMobileMode();

			// search field
			this.searchToggleEl = document.getElementById('main-search-toggle');
			if (this.searchToggleEl) {
				this.setupSearchField();
			}

		// Multiple navigation containers
		// Like with header layouts with logo in the middle
		} else {
			this.setupDesktopMode();
		}
	};

	HeaderNavigation.prototype.chooseTheMainNavigationSetup = function() {
		const buttonStyle = window.getComputedStyle(this.button);

		let currentButtonState = buttonStyle.getPropertyValue('display');
		if (this.lastButtonState === currentButtonState) {
			return;
		}

		if (currentButtonState === 'none') {
			this.setupDesktopMode();
			this.lastButtonState = 'none';
		} else {
			this.setupMobileMode();
			this.lastButtonState = 'flex';
		}

		window.addEventListener('resize', debounce(() => this.chooseTheMainNavigationSetup()));
	}

	HeaderNavigation.prototype.setupDesktopMode = function() {
		// Add event listener for top level menu items
		this.collapseAllOtherSubmenus();

		if(this.container.id === "main-navigation-container") {
			this.handleMobileNavigation('CLOSE');
		}

		this.setupSubmenuExpandListeners();
	};

	HeaderNavigation.prototype.collapseMenuAfterAchnorClick = function() {
		this.container.querySelectorAll('a[href*="#"]:not([href$="#"])').forEach((anchor) => {
			anchor.addEventListener('click', (e) => {
				this.handleMobileNavigation('CLOSE');
			})
		});
	}

	HeaderNavigation.prototype.setupMobileMode = function() {
		this.collapseAllOtherSubmenus();
		this.collapseMenuAfterAchnorClick();

		if (!this.hamburgerMenuWasSetup) {
			this.button.addEventListener('click', () => this.handleMobileNavigation('TOGGLE'));
			this.button.addEventListener('keypress', (e) => {
				var key = e.which || e.keyCode;
				if (key === 13) {
					this.handleMobileNavigation('TOGGLE');
				}
			});
			this.hamburgerMenuWasSetup = true;
		}

		this.setupSubmenuExpandListeners();
	};

	HeaderNavigation.prototype.handleMobileNavigation = function(action) {
		switch(action) {
			case 'OPEN':
				this.container.className += ' toggled';
				this.button.setAttribute( 'aria-expanded', 'true' );
				this.navigation.setAttribute( 'aria-expanded', 'true' );
				if(this.searchToggleEl) {
					this.handleSearchField('CLOSE');
				}
				if(this.isFullPageNav) {
					const headerEl = document.getElementById('page-header');
					document.querySelector('body').classList.add('navigation-overlay-oppedned');
					headerEl.classList.add('full-page-nav-opened');
					if(headerEl.classList.contains('transitioned')) {
						headerEl.classList.remove('transitioned');
						headerEl.classList.add('x-transitioned');
					}
				}
				this.handleDimmer('ADD');
				break;
			case 'CLOSE':
				this.container.classList.remove('toggled');
				this.button.setAttribute('aria-expanded', 'false');
				this.navigation.setAttribute('aria-expanded', 'false');
				this.handleDimmer('REMOVE');
				if(this.isFullPageNav) {
					const headerEl = document.getElementById('page-header');
					document.querySelector('body').classList.remove('navigation-overlay-oppedned');
					headerEl.classList.remove('full-page-nav-opened');
					if(headerEl.classList.contains('x-transitioned')) {
						headerEl.classList.remove('x-transitioned');
						headerEl.classList.add('transitioned');
					}
				}
				break;
			case 'TOGGLE':
				if (this.container.classList.contains('toggled')) {
					this.handleMobileNavigation('CLOSE');
				} else {
					this.handleMobileNavigation('OPEN');
				}
				break;
			default:
				return;
		}
	};

	HeaderNavigation.prototype.setupSubmenuExpandListeners = function() {
		if (this.subMenuListenersSet) {
			return;
		}
		const submenuToggleButtons = this.navigation.querySelectorAll('li.dropdown > .submenu-toggle-button');
		submenuToggleButtons.forEach((el) => {
			el.addEventListener('click', (event) => this.handleSubmenu(event, 'TOGGLE'));
			el.addEventListener('keydown', (event) => event.keyCode != 13 || this.handleSubmenu(event, 'TOGGLE'));
		});

		this.subMenuListenersSet = true;
	};

	HeaderNavigation.prototype.createDimmer = function() {
		const newDimmer = document.createElement('div');
		newDimmer.id = 'dimming-element';
		newDimmer.addEventListener('click', () => {
			this.handleDimmer('REMOVE');
			/* Close everything */
			this.handleMobileNavigation('CLOSE');
			if(this.searchToggleEl) {
				this.handleSearchField('CLOSE');
			}
		});
		const bodyBgEl = document.querySelector('body .body-bg');
		bodyBgEl.appendChild(newDimmer);
	};

	HeaderNavigation.prototype.handleDimmer = function(action) {
		switch(action) {
			case 'ADD':
				!document.getElementById('dimming-element') &&
					this.createDimmer();
				break;
			case 'REMOVE':
				document.getElementById('dimming-element') &&
					document.getElementById('dimming-element').remove();
				break;
			default:
				return;
		}
	};

	HeaderNavigation.prototype.collapseAllOtherSubmenus = function() {
		// TODO
		// On the same level

		return;
		// const expandedMenu = navigationEl.querySelector('li.dropdown > ul.nav-dropdown-menu.expanded');
		// if (expandedMenu) {
		// 	expandedMenu.classList.remove('expanded');
		// 	expandedMenu.style.height = '0';
		// 	const itemsInDropdown = expandedMenu.parentElement.querySelectorAll('ul.nav-dropdown-menu > li > a');
		// 	itemsInDropdown.forEach(item => {
		// 		item.setAttribute('tabindex', '-1');
		// 	});
		// }
	};

	HeaderNavigation.prototype.getTransitionDuration = function(el) {
		const transitionDuration = window.getComputedStyle(el)['transition-duration'];
		const regex = /(0\.\d*)s/;
		return regex.exec(transitionDuration)[1] * 1000;
	}

	HeaderNavigation.prototype.handleSubmenu = function(event, action) {
		event.preventDefault();
		const submenuToggleButton = event.currentTarget;
		const navItem = submenuToggleButton.parentElement;
		const currentDropdown = navItem.querySelector('ul.nav-dropdown-menu');
		const delay = this.getTransitionDuration(currentDropdown);
		const itemsInDropdown = navItem.querySelectorAll('ul.nav-dropdown-menu > li > a');

		switch(action) {
			case 'EXPAND':
				this.collapseAllOtherSubmenus();
				itemsInDropdown.forEach(item => {
					item.setAttribute('tabindex', '0');
				});
				currentDropdown.style.height = currentDropdown.scrollHeight + "px";
				navItem.classList.add('expanded');
				setTimeout((el) => {
					el.style.height = '';
				}, delay, currentDropdown);
				break;
			case 'COLLAPSE':
				currentDropdown.style.height = currentDropdown.scrollHeight + "px";
				setTimeout((el) => {
					el.style.height = '';
					navItem.classList.remove('expanded');
				}, 16, currentDropdown);

				itemsInDropdown.forEach(item => {
					item.setAttribute('tabindex', '-1');
				});
				break;
			case 'TOGGLE':
				if (navItem.classList.contains('expanded')) {
					this.handleSubmenu(event, 'COLLAPSE');
				} else {
					this.handleSubmenu(event, 'EXPAND');
				}
				break;
			default:
				return;
		}
	};

	// handle search field
	HeaderNavigation.prototype.handleSearchField = function(action) {
		const searchContainer = this.searchToggleEl.parentNode.parentNode;
		const searchFormEl = searchContainer.querySelector('form');

		switch(action) {
			case 'OPEN':
				searchContainer.classList.add('expanded');
				searchFormEl.querySelector('#search-form-tx-indexedsearch-searchbox-sword').focus();
				this.handleMobileNavigation('CLOSE');
				this.handleDimmer('ADD');
				break;
			case 'CLOSE':
				searchFormEl.querySelector('#search-form-tx-indexedsearch-searchbox-sword').blur();
				searchContainer.classList.remove('expanded');
				this.handleDimmer('REMOVE');
				break;
			case 'TOGGLE':
				if (searchContainer.classList.contains('expanded')) {
					this.handleSearchField('CLOSE');
				} else {
					this.handleSearchField('OPEN');
				}
				break;
			default:
				break;
		}
	};

	HeaderNavigation.prototype.setupSearchField = function() {
		this.searchToggleEl.addEventListener('click', () => this.handleSearchField('TOGGLE'));
		this.searchToggleEl.addEventListener('keydown', (e) => {
			var key = e.which || e.keyCode;
			if (key === 13) {
				e.preventDefault();
				this.handleSearchField('TOGGLE');
			}
		});
	};


	window.HeaderNavigation = HeaderNavigation;

	const headerNavigationContainers = document.querySelectorAll('.header-navigation, #full-page-navigation');
	const headerNavInstances = [];

	headerNavigationContainers.forEach(navCont => {
		headerNavInstances.push(new HeaderNavigation(navCont))
	});

	// End of esential header navigation functions


	// handle sub menu expansion direction
	(function (){
		const dropdownEl = document.querySelector('.level-2.nav-dropdown-menu');
		const baseFrameEl = document.querySelector('.section .frame-container');

		if (!dropdownEl || !baseFrameEl) {
			return;
		}

		// Get computed values and parse them
		// same as $m-nav-min-width * 16
		const dropdownMinWidth = parseInt(getComputedStyle(dropdownEl).minWidth, 10);
		const minPaddingFromViewport = parseInt(getComputedStyle(baseFrameEl).paddingRight, 10);

		const dropdownElements = document.querySelectorAll('.level-2.nav-dropdown-menu');

		const calculateSubmenuPositions = () => {
			dropdownElements.forEach(el => {
				const anchorBoundBox = el.parentNode.querySelector('a').getBoundingClientRect();
				if ((window.innerWidth - anchorBoundBox.left) < dropdownMinWidth + minPaddingFromViewport) {
					el.classList.add('absolute-position-align-to-right');
				} else {
					el.classList.remove('absolute-position-align-to-right');
				}
			});
		}

		window.addEventListener('resize', debounce(() => calculateSubmenuPositions(), 20));
		calculateSubmenuPositions();
	})();

/*
	// Handle language select
	(function(){
		const languageContainer = document.querySelector('.language-switcher-layout-select');
		if (!languageContainer) {
			return;
		}

		const currentLangEl = languageContainer.querySelector('li.active > a');

		if (currentLangEl) {
			currentLangEl.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				languageContainer.classList.toggle('expanded');

				document.addEventListener('click', handleClosingLanguageMenu);
			});

			const handleClosingLanguageMenu = () => {
				languageContainer.classList.remove('expanded');
				document.removeEventListener('click', handleClosingLanguageMenu);
			}
		}
	})( );
*/

	// END

})(window);
