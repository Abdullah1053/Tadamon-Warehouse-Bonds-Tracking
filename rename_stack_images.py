#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
rename_stack_images.py

Renames raw bond images in a stack folder based on database serial numbers from tadamon.sql.
Supports English stack ID folders (e.g., 'bonds_images/24') as well as stack names (e.g., 'دفتر 35').
Skips physically missing bonds (is_missing=1) so numbering aligns with physical pages.

Usage:
    python rename_stack_images.py --folder bonds_images/24
    python rename_stack_images.py --folder 24
"""

import os
import re
import sys
import argparse
from pathlib import Path

sys.stdout.reconfigure(encoding='utf-8')


def parse_sql_data(sql_path, folder_identifier):
    """
    Parses tadamon.sql to find the stack matching either stack ID or stack name,
    and returns the stack metadata and its ordered bonds.
    """
    if not os.path.exists(sql_path):
        raise FileNotFoundError(f"SQL file not found: {sql_path}")

    with open(sql_path, 'r', encoding='utf-8') as f:
        sql_text = f.read()

    # Find stack matching folder_identifier (by ID or by name)
    stack_matches = re.findall(r"\((\d+),\s*'([^']+)',\s*(\d+),\s*(\d+)", sql_text)
    matched_stack = None

    folder_clean = str(folder_identifier).strip()
    is_id = folder_clean.isdigit()

    for sm in stack_matches:
        s_id, s_name, start_s, end_s = sm
        if is_id and int(s_id) == int(folder_clean):
            matched_stack = {
                'id': int(s_id),
                'name': s_name,
                'start_serial': int(start_s),
                'end_serial': int(end_s)
            }
            break
        elif s_name.strip() == folder_clean:
            matched_stack = {
                'id': int(s_id),
                'name': s_name,
                'start_serial': int(start_s),
                'end_serial': int(end_s)
            }
            break

    if not matched_stack:
        raise ValueError(f"Stack with identifier '{folder_identifier}' not found in {sql_path}")

    # Extract bonds for this stack
    bonds_section_start = sql_text.find("INSERT INTO `bonds`")
    bonds_section_end = sql_text.find("CREATE TABLE IF NOT EXISTS `bond_items`")
    bonds_text = sql_text[bonds_section_start:bonds_section_end]

    pattern = re.compile(
        r"\(\s*(\d+)\s*,\s*(\d+)\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*(NULL|'[^']*')\s*,\s*(NULL|'(?:[^'\\]|\\.)*')\s*,\s*(\d+)\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*\)",
        re.DOTALL
    )

    stack_bonds = []
    for m in pattern.finditer(bonds_text):
        b_id, stack_id, serial, date, op, recv, car, note, is_missing, c_at, u_at = m.groups()
        if int(stack_id) == matched_stack['id']:
            stack_bonds.append({
                'id': int(b_id),
                'serial': int(serial) if serial.isdigit() else serial,
                'op': op,
                'recv': recv,
                'note': note,
                'is_missing': int(is_missing)
            })

    stack_bonds.sort(key=lambda x: int(x['serial']) if str(x['serial']).isdigit() else x['serial'])
    return matched_stack, stack_bonds


def get_sorted_images(folder_path):
    """Returns image files sorted naturally by numeric index."""
    valid_exts = ('.jpg', '.jpeg', '.png', '.webp', '.avif')
    files = [f for f in os.listdir(folder_path) if f.lower().endswith(valid_exts)]

    def natural_sort_key(filename):
        nums = re.findall(r'\d+', filename)
        return int(nums[-1]) if nums else filename.lower()

    files.sort(key=natural_sort_key)
    return files


def rename_images_in_stack(folder_path, sql_path="tadamon.sql", dry_run=False):
    target = Path(folder_path)
    if not target.is_dir():
        # Check inside bonds_images
        candidate = Path("bonds_images") / folder_path
        if candidate.is_dir():
            target = candidate
        else:
            print(f"[ERROR] Folder '{folder_path}' does not exist.")
            return

    folder_identifier = target.name
    print(f"Resolving stack for folder: '{target}' (Identifier: {folder_identifier})")

    matched_stack, stack_bonds = parse_sql_data(sql_path, folder_identifier)
    print(f"Matched Stack: ID={matched_stack['id']}, Name='{matched_stack['name']}', Serials={matched_stack['start_serial']}..{matched_stack['end_serial']}")

    # Filter out missing bonds (is_missing == 1) because physical image doesn't exist
    present_bonds = [b for b in stack_bonds if b['is_missing'] == 0]
    missing_bonds = [b for b in stack_bonds if b['is_missing'] == 1]

    print(f"Total Bonds: {len(stack_bonds)} | Present (Physical Images): {len(present_bonds)} | Missing: {len(missing_bonds)}")
    if missing_bonds:
        print(f"Skipping missing serials: {[b['serial'] for b in missing_bonds]}")

    images = get_sorted_images(target)
    print(f"Found {len(images)} images in directory.")

    if len(images) != len(present_bonds):
        print(f"[!] Warning: Number of images ({len(images)}) does not match expected present bonds ({len(present_bonds)}).")
        proceed = input("Do you still want to proceed? [y/N]: ").strip().lower()
        if proceed not in ('y', 'yes'):
            print("Aborted.")
            return

    print("\n--- Renaming Plan ---")
    plan = []
    for img_file, bond in zip(images, present_bonds):
        ext = os.path.splitext(img_file)[1]
        new_name = f"{bond['serial']}{ext}"
        src = target / img_file
        dst = target / new_name
        plan.append((src, dst))
        print(f"  {img_file} -> {new_name} (Serial: {bond['serial']})")

    if dry_run:
        print("\n[Dry Run] No files were renamed.")
        return

    # Execute renaming safely (using temporary names first to prevent collisions)
    print("\nExecuting renaming...")
    temp_plan = []
    for idx, (src, dst) in enumerate(plan):
        temp_name = target / f"__tmp_{idx}_{dst.name}"
        os.rename(src, temp_name)
        temp_plan.append((temp_name, dst))

    for temp_src, dst in temp_plan:
        os.rename(temp_src, dst)

    print(f"[SUCCESS] Renamed {len(plan)} images successfully in '{target}'.")


def main():
    parser = argparse.ArgumentParser(description="Rename bond images based on database serials.")
    parser.add_argument(
        '--folder',
        type=str,
        default="bonds_images/24",
        help="Path to folder or Stack ID (default: 'bonds_images/24')"
    )
    parser.add_argument(
        '--sql',
        type=str,
        default="tadamon.sql",
        help="Path to tadamon.sql database dump (default: 'tadamon.sql')"
    )
    parser.add_argument(
        '--dry-run',
        action='store_true',
        help="Show renaming plan without modifying files"
    )
    args = parser.parse_args()
    rename_images_in_stack(args.folder, args.sql, args.dry_run)


if __name__ == '__main__':
    main()