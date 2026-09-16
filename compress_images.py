#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Script to optimize and compress bond images in a specified folder.
Preserves original image resolution (dimensions) while minimizing file size.
Supports JPEG optimization (lossless Huffman tables + progressive encoding)
and optional WebP conversion for maximum compression.
"""

import os
import sys
import argparse
from PIL import Image

sys.stdout.reconfigure(encoding='utf-8')

VALID_EXTENSIONS = ('.jpg', '.jpeg', '.png')

def format_size(num_bytes):
    """Converts bytes to human readable string."""
    for unit in ['B', 'KB', 'MB', 'GB']:
        if abs(num_bytes) < 1024.0:
            return f"{num_bytes:3.1f} {unit}"
        num_bytes /= 1024.0
    return f"{num_bytes:.1f} TB"

def compress_image(src_path, dest_path, quality=75, to_webp=False):
    """
    Compresses a single image while strictly preserving its original resolution.
    Only replaces if the new file is smaller than the original.
    """
    orig_size = os.path.getsize(src_path)
    
    with Image.open(src_path) as img:
        orig_resolution = img.size  # (width, height)
        
        # Determine output format and settings
        if to_webp:
            # Ensure RGB mode for WebP if image is in RGBA or other modes
            if img.mode in ('RGBA', 'LA') and not to_webp:
                save_mode = img.convert('RGB')
            else:
                save_mode = img
            
            dest_dir = os.path.dirname(dest_path)
            base_name = os.path.splitext(os.path.basename(dest_path))[0]
            actual_dest = os.path.join(dest_dir, f"{base_name}.webp")
            
            # Save as WebP with method 6 (highest compression efficiency)
            save_mode.save(actual_dest, 'WEBP', quality=quality, method=6)
        else:
            actual_dest = dest_path
            # Convert to RGB if palette or other modes to ensure standard JPEG
            if img.mode != 'RGB':
                rgb_img = img.convert('RGB')
            else:
                rgb_img = img

            # Use a temporary file first to compare sizes
            temp_dest = actual_dest + ".tmp"
            rgb_img.save(
                temp_dest,
                'JPEG',
                quality=quality,
                optimize=True,
                progressive=True
            )
            
            new_size = os.path.getsize(temp_dest)
            
            # If the compressed version is smaller, keep it; otherwise keep original
            if new_size < orig_size:
                if os.path.exists(actual_dest):
                    os.remove(actual_dest)
                os.rename(temp_dest, actual_dest)
            else:
                if os.path.exists(temp_dest):
                    os.remove(temp_dest)
                if src_path != actual_dest:
                    import shutil
                    shutil.copy2(src_path, actual_dest)
                return orig_size, orig_size, orig_resolution, False

    new_size = os.path.getsize(actual_dest)
    is_reduced = new_size < orig_size
    return orig_size, new_size, orig_resolution, is_reduced

def process_folder(folder_path, output_folder=None, quality=75, to_webp=False, dry_run=False):
    """
    Compresses all images in folder_path.
    """
    if not os.path.exists(folder_path):
        candidate = os.path.join("bonds_images", folder_path)
        if os.path.exists(candidate):
            folder_path = candidate
        else:
            print(f"Error: Folder '{folder_path}' does not exist.")
            sys.exit(1)

    # Collect image files
    files = [f for f in os.listdir(folder_path) if f.lower().endswith(VALID_EXTENSIONS)]
    if not files:
        print(f"No image files found in '{folder_path}'.")
        return

    # Natural sorting of files
    import re
    def natural_sort_key(s):
        return [int(text) if text.isdigit() else text.lower() for text in re.split(r'(\d+)', s)]
    files.sort(key=natural_sort_key)

    in_place = (output_folder is None)
    if not in_place and not dry_run:
        os.makedirs(output_folder, exist_ok=True)

    target_desc = "in-place (overwriting originals)" if in_place else f"output to '{output_folder}'"
    fmt_desc = "WebP" if to_webp else f"JPEG (Quality {quality}, optimize=True, progressive=True)"
    
    print("=" * 70)
    print(f"Folder: '{folder_path}'")
    print(f"Total images found: {len(files)}")
    print(f"Target destination: {target_desc}")
    print(f"Target format/settings: {fmt_desc}")
    print(f"Resolution rule: 100% PRESERVED (No downscaling)")
    if dry_run:
        print("[DRY RUN MODE - No files will be modified]")
    print("=" * 70)

    total_orig_bytes = 0
    total_new_bytes = 0
    results = []

    for idx, filename in enumerate(files, 1):
        src_file = os.path.join(folder_path, filename)
        orig_bytes = os.path.getsize(src_file)
        total_orig_bytes += orig_bytes

        if dry_run:
            # Measure potential size using a temp compression
            with Image.open(src_file) as img:
                res = img.size
                temp_test = os.path.join(folder_path, f".test_tmp_{idx}")
                if to_webp:
                    img.save(temp_test, 'WEBP', quality=quality, method=6)
                else:
                    rgb_img = img.convert('RGB') if img.mode != 'RGB' else img
                    rgb_img.save(temp_test, 'JPEG', quality=quality, optimize=True, progressive=True)
                sim_bytes = os.path.getsize(temp_test)
                os.remove(temp_test)
                
                final_bytes = min(orig_bytes, sim_bytes)
                total_new_bytes += final_bytes
                saved_bytes = orig_bytes - final_bytes
                ratio = (saved_bytes / orig_bytes) * 100 if orig_bytes else 0
                results.append((filename, orig_bytes, final_bytes, res, ratio))
                print(f"[{idx:02d}/{len(files):02d}] {filename:<15} ({res[0]}x{res[1]}): {format_size(orig_bytes)} -> {format_size(final_bytes)} (-{ratio:.1f}%)")
        else:
            if in_place:
                dest_file = src_file
            else:
                dest_file = os.path.join(output_folder, filename)

            orig_s, new_s, res, reduced = compress_image(src_file, dest_file, quality=quality, to_webp=to_webp)
            total_new_bytes += new_s
            saved_bytes = orig_s - new_s
            ratio = (saved_bytes / orig_s) * 100 if orig_s else 0
            results.append((filename, orig_s, new_s, res, ratio))
            print(f"[{idx:02d}/{len(files):02d}] {filename:<15} ({res[0]}x{res[1]}): {format_size(orig_s)} -> {format_size(new_s)} (-{ratio:.1f}%)")

    # Final Summary
    total_saved = total_orig_bytes - total_new_bytes
    total_ratio = (total_saved / total_orig_bytes) * 100 if total_orig_bytes else 0
    print("\n" + "=" * 70)
    print("COMPRESSION SUMMARY:")
    print(f"Total images processed:  {len(files)}")
    print(f"Original total size:     {format_size(total_orig_bytes)} ({total_orig_bytes:,} bytes)")
    print(f"New total size:          {format_size(total_new_bytes)} ({total_new_bytes:,} bytes)")
    print(f"Total space saved:       {format_size(total_saved)} ({total_ratio:.1f}% reduction)")
    print("=" * 70)

def main():
    parser = argparse.ArgumentParser(
        description="Compress and minimize bond image sizes without changing resolution."
    )
    parser.add_argument(
        '--folder',
        type=str,
        default=r"bonds_images\24",
        help="Path to folder or Stack ID (default: 'bonds_images\\24')"
    )
    parser.add_argument(
        '--output',
        type=str,
        default=None,
        help="Output folder (optional; if not specified, compresses in-place)"
    )
    parser.add_argument(
        '--quality',
        type=int,
        default=75,
        help="Compression quality (1-95, default: 75). Keeps handwriting razor-sharp."
    )
    parser.add_argument(
        '--to-webp',
        action='store_true',
        help="Convert images to modern WebP format for up to 40-50% size reduction."
    )
    parser.add_argument(
        '--dry-run',
        action='store_true',
        help="Simulate compression and show size reduction without changing files."
    )

    args = parser.parse_args()
    process_folder(
        folder_path=args.folder,
        output_folder=args.output,
        quality=args.quality,
        to_webp=args.to_webp,
        dry_run=args.dry_run
    )

if __name__ == '__main__':
    main()
