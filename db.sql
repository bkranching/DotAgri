CREATE TABLE tlds (
	id INTEGER PRIMARY KEY,
	name VARCHAR(63) NOT NULL,
	allow_register INTEGER DEFAULT 1
);
	
CREATE TABLE domains (
	id INTEGER PRIMARY KEY,
	domain_name VARCHAR(63) NOT NULL,
	name VARCHAR(20) NOT NULL,
	user_id INTEGER NOT NULL,
	private_contact INTEGER NOT NULL,
	public_contact INTEGER,
	registered DATE NOT NULL,
	expires DATE,
	updated DATE NOT NULL
);

create table users (
	id INTEGER PRIMARY KEY,
	username VARCHAR(32) NOT NULL,
	password VARCHAR(255) NOT NULL,
	admin_contact INTEGER NOT NULL,
	registered DATE NOT NULL,
	verified INTEGER NOT NULL
);

create table contacts (
	id INTEGER PRIMARY KEY,
	user_id INTEGER NOT NULL,
	email_address VARCHAR(255),
	pgp_key VARCHAR(8192),
	verified INTEGER NOT NULL,
	verification_token INTEGER
);

--See: RFC 1035
create table records (
	id INTEGER PRIMARY KEY,
	domain_id INTEGER NOT NULL,
	name VARCHAR(63) NOT NULL,
	record_type INTEGER NOT NULL,
	record_class INTEGER NOT NULL, 
	ttl INTEGER DEFAULT 7200,
	rdata VARCHAR(65536) -- rdata can theoretically be up to 2^16 bytes
);

