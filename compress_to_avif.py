#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Script to compress images in a folder to AVIF (and/or WebP) format.
Preserves 100% of original image resolution while delivering maximum file size reduction.
"""

import os
import sys
import argparse

# Support both pillow-avif-plugin and pillow_heif for AVIF encoding
try:
    import pillow_avif  # noqa: F401
    AVIF_SUPPORTED = True
except ImportError:
    try:
        # pyrefly: ignore [missing-import]
        import pillow_heif
        pillow_heif.register_avif_opener()
        AVIF_SUPPORTED = True
    except ImportError:
        AVIF_SUPPORTED = False

from PIL import Image

VALID_INPUT_EXTENSIONS = ('.jpg', '.jpeg', '.png', '.bmp', '.tiff')

def format_size(num_bytes):
    """Converts bytes to human-readable string (KB, MB)."""
    for unit in ['B', 'KB', 'MB', 'GB']:
        if abs(num_bytes) < 1024.0:
            return f"{num_bytes:3.1f} {unit}"
        num_bytes /= 1024.0
    return f"{num_bytes:.1f} TB"

def process_folder(folder_path, output_folder=None, format_type="avif", quality=80, keep_originals=True):
    if not os.path.exists(folder_path):
        candidate = os.path.join("bonds_images", folder_path)
        if os.path.exists(candidate):
            folder_path = candidate
        else:
            print(f"Error: Folder '{folder_path}' does not exist.")
            return

    # Check AVIF plugin
    if format_type.lower() == "avif" and not AVIF_SUPPORTED:
        print("\n" + "=" * 60)
        print("WARNING: AVIF plugin is not installed!")
        print("Please install it by running:")
        print("    pip install pillow-avif-plugin")
        print("or:")
        print("    pip install pillow-heif")
        print("=" * 60 + "\n")
        return

    # Determine destination folder
    if output_folder is None:
        output_folder = os.path.join(folder_path, f"optimized_{format_type.lower()}")
    os.makedirs(output_folder, exist_ok=True)

    # Find all images
    files = [f for f in os.listdir(folder_path) if f.lower().endswith(VALID_INPUT_EXTENSIONS)]
    if not files:
        print(f"No valid images found in '{folder_path}'.")
        return

    # Natural sorting
    import re
    def natural_sort_key(s):
        return [int(t) if t.isdigit() else t.lower() for t in re.split(r'(\d+)', s)]
    files.sort(key=natural_sort_key)

    ext = f".{format_type.lower()}"
    save_format = "AVIF" if format_type.lower() == "avif" else "WEBP"

    print("=" * 70)
    print(f"Input Folder:      {folder_path}")
    print(f"Output Folder:     {output_folder}")
    print(f"Total Images:      {len(files)}")
    print(f"Target Format:     {save_format} (Quality: {quality})")
    print(f"Resolution Rule:   100% Original Resolution Preserved (No resizing)")
    print("=" * 70)

    total_orig_bytes = 0
    total_new_bytes = 0

    for idx, filename in enumerate(files, 1):
        src_file = os.path.join(folder_path, filename)
        orig_bytes = os.path.getsize(src_file)
        total_orig_bytes += orig_bytes

        base_name = os.path.splitext(filename)[0]
        dest_filename = f"{base_name}{ext}"
        dest_file = os.path.join(output_folder, dest_filename)

        with Image.open(src_file) as img:
            res = img.size  # (width, height)
            
            # Save in chosen format without resizing
            if save_format == "AVIF":
                img.save(dest_file, "AVIF", quality=quality)
            else:
                img.save(dest_file, "WEBP", quality=quality, method=6)

        new_bytes = os.path.getsize(dest_file)
        total_new_bytes += new_bytes

        reduction = (1 - new_bytes / orig_bytes) * 100 if orig_bytes else 0
        print(f"[{idx:02d}/{len(files):02d}] {filename} ({res[0]}x{res[1]}) -> {dest_filename}: {format_size(orig_bytes)} -> {format_size(new_bytes)} (-{reduction:.1f}%)")

        if not keep_originals and output_folder == folder_path:
            os.remove(src_file)

    # Summary
    total_saved = total_orig_bytes - total_new_bytes
    total_reduction = (total_saved / total_orig_bytes) * 100 if total_orig_bytes else 0

    print("\n" + "=" * 70)
    print("FINISHED SUCCESSFULLY!")
    print(f"Total images processed: {len(files)}")
    print(f"Original total size:    {format_size(total_orig_bytes)}")
    print(f"New total size:         {format_size(total_new_bytes)}")
    print(f"Total storage saved:    {format_size(total_saved)} ({total_reduction:.1f}% reduction)")
    print(f"Optimized files saved to: {output_folder}")
    print("=" * 70)

def main():
    parser = argparse.ArgumentParser(description="Batch convert & compress images to AVIF or WebP.")
    parser.add_argument(
        "--folder",
        type=str,
        default=r"bonds_images\24",
        help="Path to folder or Stack ID (default: 'bonds_images\\24')"
    )
    parser.add_argument(
        "--output",
        type=str,
        default=None,
        help="Custom output folder (default: creates 'optimized_avif' or 'optimized_webp' subfolder)"
    )
    parser.add_argument(
        "--format",
        type=str,
        choices=["avif", "webp"],
        default="avif",
        help="Target compression format: 'avif' (default) or 'webp'"
    )
    parser.add_argument(
        "--quality",
        type=int,
        default=80,
        help="Quality level from 1 to 100 (default: 80, optimal for size/quality)"
    )

    args = parser.parse_args()
    process_folder(
        folder_path=args.folder,
        output_folder=args.output,
        format_type=args.format,
        quality=args.quality
    )

if __name__ == '__main__':
    main()
