#!/usr/bin/env python3
import argparse
import os
import subprocess
import sys
import time
from pathlib import Path
from typing import Optional


def project_root() -> Path:
    return Path(__file__).resolve().parent.parent


def resolve_sql_path(sql_path_arg: Optional[str]) -> Path:
    root = project_root()
    default_sql = root / "doc" / "chikusandb.sql"

    if not sql_path_arg:
        return default_sql

    candidate = Path(sql_path_arg)
    if not candidate.is_absolute():
        candidate = root / candidate
    return candidate


def run_migration(sql_file: Path, service: str, user: str, password: str, database: str) -> int:
    if not sql_file.exists():
        print(f"[ERROR] SQL file not found: {sql_file}")
        return 1

    cmd = [
        "docker",
        "compose",
        "exec",
        "-T",
        service,
        "mysql",
        f"-u{user}",
        f"-p{password}",
        database,
    ]

    ping_cmd = [
        "docker",
        "compose",
        "exec",
        "-T",
        service,
        "mysqladmin",
        f"-u{user}",
        f"-p{password}",
        "ping",
        "--silent",
    ]

    print("[INFO] Running migration...")
    print(f"[INFO] SQL file: {sql_file}")
    print(f"[INFO] Target service/database: {service}/{database}")

    try:
        for _ in range(30):
            ping = subprocess.run(ping_cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=False)
            if ping.returncode == 0:
                break
            time.sleep(1)
        else:
            print("[ERROR] MySQL service is not ready after waiting 30 seconds.")
            return 1

        with sql_file.open("rb") as input_sql:
            completed = subprocess.run(cmd, stdin=input_sql, check=False)
    except FileNotFoundError:
        print("[ERROR] 'docker' command was not found.")
        print("        Install Docker Desktop and ensure 'docker compose' is available.")
        return 1

    if completed.returncode != 0:
        print(f"[ERROR] Migration failed with exit code {completed.returncode}.")
        return completed.returncode

    print("[INFO] Migration completed successfully.")
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description="Apply MySQL migration SQL to Docker MySQL service.")
    parser.add_argument("--sql", help="Path to SQL file (default: doc/chikusandb.sql)")
    parser.add_argument("--service", default=os.getenv("MYSQL_SERVICE", "mysql"), help="Docker Compose service name (default: mysql)")
    parser.add_argument("--user", default=os.getenv("MYSQL_USER", "root"), help="MySQL user (default: root)")
    parser.add_argument("--password", default=os.getenv("MYSQL_PASSWORD", "root"), help="MySQL password (default: root)")
    parser.add_argument("--database", default=os.getenv("MYSQL_DATABASE", "employee"), help="MySQL database name (default: employee)")

    args = parser.parse_args()
    sql_file = resolve_sql_path(args.sql)

    return run_migration(
        sql_file=sql_file,
        service=args.service,
        user=args.user,
        password=args.password,
        database=args.database,
    )


if __name__ == "__main__":
    sys.exit(main())
