# Adding your module

Making your module work with the User Guide is simple.

First, copy this config and place in it `<module>/config/userguide.php`, replacing anything in `<>` with the appropriate values:

	return array
	(
		// Leave this alone
		'modules' => array(

			/*
			 * The path to this module's userguide pages, without the 'guide/'.
			 *
			 * For example,  '/guide/modulename/' would be 'modulename'
			 */
			'<modulename>' => array(

				// Whether this module's user guide pages should be shown
				'enabled' => TRUE,

				// The name that should show up on the user guide index page
				'name' => '<Module Name>',

				// A short description of this module, shown on the index page
				'description' => '<Description goes here>',

				// Copyright message, shown in the footer for this module
				'copyright' => '&copy; 2012 <Your Name>',
			)
		),

		/*
		 * If you use transparent extensions outside the Kohana_ namespace,
		 * add your class prefix here. Both common Kohana naming conventions are
		 * excluded:
		 *   - Modulename extends Modulename_Core
		 *   - Foo extends Modulename_Foo
		 *
		 * For example, if you use Modulename_<class_name> for your base classes
		 * then you would define:
		 */
		'transparent_prefixes' => array(
			'Modulename' => TRUE,
		)
	);

Next, create a folder in your module directory called `guide/<modulename>` and create `index.md` and `menu.md`.  The contents of `index.md` is what is shown on the index page of your module.

## Creating the side menu

The contents of the `menu.md` file is what shows up in the side column and should be formatted like this:

	## [Module Name]()
	 - [Page name](page-path)
	 - [This is a Category](category)
		 - [Sub Page](category/sub-page)
		 - [Another](category/another)
			 - [Sub sub page](category/another/sub-page)
	 - Categories do not have to be a link to a page
		 - [Etcetera](etc)

You can have items that are not linked pages (a category that doesn't have a corresponding page).

Guide pages can be named or arranged any way you want within that folder (with the exception of `index.md` and `menu.md` which must appear directly below the `guide/` folder).

## Formatting page titles and links

Page paths are relative to `guide/<modulename>`.  So `[Page name](page-name)` would look for `guide/<modulename>/page-name.md` and `[Another](category/another)` would look for `guide/<modulename>/category/another.md`.

The breadcrumbs and page titles are pulled from the `menu.md` file, not the filenames or paths.

To link to the `index.md` page, you should have an empty link, e.g. `[Module Name]()`.  Do not include `.md` in your links.

All user guide pages use [Markdown](markdown).