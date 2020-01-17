--
-- Remove user stylesheets
--

DROP TABLE user_stylesheet;

ALTER TABLE user
  DROP display_stylesheet_id,
  DROP export_stylesheet_id,
  DROP print_stylesheet_id;

