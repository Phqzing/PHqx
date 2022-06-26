-- #! sqlite


-- #{ phqx

-- #    { init
CREATE TABLE IF NOT EXISTS settings (
    name VARCHAR(16) NOT NULL PRIMARY KEY,
    killaura TEXT,
    reach TEXT,
    speed TEXT,
    antikb BOOLEAN
);
-- #    }


-- #    { insert
-- #    :name string
INSERT OR REPLACE INTO settings
(name, killaura, reach, speed, antikb)
VALUES
(:name, "none", "none", "none", false);
-- #    }


-- #    { get
-- #    :name string
SELECT * FROM settings WHERE name = :name;
-- #    }


-- #    { save
-- #    :name string
-- #    :killaura string
-- #    :reach string
-- #    :speed string
-- #    :antikb bool
UPDATE settings SET killaura = :killaura, reach = :reach, speed = :speed, antikb = :antikb WHERE name = :name;
-- #    }

-- #}