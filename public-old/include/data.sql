
INSERT INTO user (id, username, fullname, password, display_stylesheet_id, print_stylesheet_id, export_stylesheet_id) VALUES ('0', 'system', 'System', ENCRYPT('changeme'), 1, 2, 3);

INSERT INTO user_stylesheet (id, user_id, sheet_name, sheet_type, public, contents) VALUES (
	1, 0, 'Default', 'display', 'y',
	'td {\n\tvertical-align: top;\n}\n\n.section {\n\tfont-weight: bold;\n\tfont-variant: small-caps;\n}\n\n.high_priority {\n\tcolor: blue;\n}\n\n.estimate {\n\tcolor: red;\n}\n\n.closed {\n\ttext-decoration: line-through;\n}\n\n.estimate_total {\n\tcolor: red;\n\tfont-weight: bold;\n}\n\n'
);

INSERT INTO user_stylesheet (id, user_id, sheet_name, sheet_type, public, contents) VALUES (
	2, 0, 'Default', 'print', 'y',
	'body {\n\tmargin: 0in;\n}\n\ninput {\n\twidth: 30px; \n\theight: 12px;\n}\n\ntd {\n\tvertical-align: top;\n\tfont-size: 10pt;\n}\n\n.section {\n\tfont-weight: bold;\n\tfont-variant: small-caps;\n}\n\n.high_priority {\n\tcolor: blue;\n}\n\n.estimate {\n\tcolor: red;\n}\n\n.closed {\n\ttext-decoration: line-through;\n}\n\n.estimate_total {\n\tcolor: red;\n\tfont-weight: bold;\n}\n\n'
);

INSERT INTO user_stylesheet (id, user_id, sheet_name, sheet_type, public, contents) VALUES (
	3, 0, 'Default', 'export', 'y',
	'td {\n\tvertical-align: top;\n}\n\n.section {\n\tfont-weight: bold;\n\tfont-variant: small-caps;\n}\n\n.high_priority {\n\tcolor: blue;\n}\n\n.estimate {\n\tcolor: red;\n}\n\n.closed {\n\ttext-decoration: line-through;\n}\n\n.estimate_total {\n\tcolor: red;\n\tfont-weight: bold;\n}\n\n'
);

