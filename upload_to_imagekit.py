"""
upload_to_imagekit.py

Uploads bond images from a selected folder to ImageKit using the ImageKit Python SDK,
and automatically calls `update_bond_links.php` to save the resulting image URLs 
into the database for matching bond serials.

Requirements:
    pip install imagekitio
"""

import os
import sys
import json
import subprocess
from pathlib import Path

# =====================================================================
# 1. ImageKit API Credentials
# =====================================================================
# You can paste your credentials here directly, or set them in your .env file
# or system environment variables (IMAGEKIT_PRIVATE_KEY, etc.)
IMAGEKIT_PRIVATE_KEY = os.environ.get("IMAGEKIT_PRIVATE_KEY", "private_h6GCTsXgdxJPtQeehd1+om9tZRc=")
IMAGEKIT_PUBLIC_KEY = os.environ.get("IMAGEKIT_PUBLIC_KEY", "public_zJIPizHLFgKdh7oxbW8qvWNtAGw=")
IMAGEKIT_URL_ENDPOINT = os.environ.get("IMAGEKIT_URL_ENDPOINT", "https://ik.imagekit.io/tadamon")


def load_env_file():
    """Attempts to read ImageKit credentials from .env if still using placeholders."""
    env_path = Path(".env")
    if not env_path.exists():
        return
    
    with open(env_path, "r", encoding="utf-8", errors="ignore") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, val = line.split("=", 1)
            key = key.strip()
            val = val.strip().strip("\"'")
            if key == "IMAGEKIT_PRIVATE_KEY" and IMAGEKIT_PRIVATE_KEY == "your_private_key_here":
                os.environ["IMAGEKIT_PRIVATE_KEY"] = val
            elif key == "IMAGEKIT_PUBLIC_KEY" and IMAGEKIT_PUBLIC_KEY == "your_public_key_here":
                os.environ["IMAGEKIT_PUBLIC_KEY"] = val
            elif key == "IMAGEKIT_URL_ENDPOINT" and IMAGEKIT_URL_ENDPOINT == "https://ik.imagekit.io/your_imagekit_id":
                os.environ["IMAGEKIT_URL_ENDPOINT"] = val


def get_imagekit_client():
    """Initializes and returns the ImageKit client supporting both SDK v5+ and v4."""
    try:
        from imagekitio import ImageKit
    except ImportError:
        print("\n[ERROR] The ImageKit SDK is not installed.")
        print("Please run: pip install imagekitio\n")
        sys.exit(1)

    private_key = os.environ.get("IMAGEKIT_PRIVATE_KEY", IMAGEKIT_PRIVATE_KEY)
    public_key = os.environ.get("IMAGEKIT_PUBLIC_KEY", IMAGEKIT_PUBLIC_KEY)
    url_endpoint = os.environ.get("IMAGEKIT_URL_ENDPOINT", IMAGEKIT_URL_ENDPOINT)

    # Prompt if still default placeholder
    if private_key == "your_private_key_here" or not private_key:
        print("\n--- ImageKit Configuration Required ---")
        private_key = input("Enter your ImageKit Private Key: ").strip()
        public_key = input("Enter your ImageKit Public Key (optional): ").strip()
        url_endpoint = input("Enter your ImageKit URL Endpoint (optional): ").strip()

    # SDK v5 initializes with private_key only; older SDKs require public_key and url_endpoint
    try:
        # Try new SDK pattern (v5+)
        client = ImageKit(private_key=private_key)
        return client
    except TypeError:
        # Fallback to older SDK pattern (v4/v3)
        client = ImageKit(
            private_key=private_key,
            public_key=public_key,
            url_endpoint=url_endpoint
        )
        return client


def select_folder():
    """Prompts user to choose an image folder or lists available folders."""
    # Check if folder or stack ID was provided via command line
    if len(sys.argv) > 1:
        raw_arg = sys.argv[1].strip()
        folder = Path(raw_arg)
        if folder.is_dir():
            return folder
        # Check inside bonds_images
        candidate = Path("bonds_images") / raw_arg
        if candidate.is_dir():
            return candidate
        print(f"[!] Warning: Neither '{raw_arg}' nor '{candidate}' is a valid directory.")

    # Search for stack folders in bonds_images (and legacy صور السندات if exists)
    subdirs = []
    for base_dir in [Path("bonds_images"), Path("صور السندات")]:
        if base_dir.is_dir():
            subdirs.extend([p for p in base_dir.iterdir() if p.is_dir()])

    print("\n=======================================================")
    print(" Select Stack Folder to Upload to ImageKit")
    print("=======================================================")
    if subdirs:
        print("Available Stack Folders found:")
        for idx, s in enumerate(subdirs, 1):
            print(f"  [{idx}] {s}  (Stack ID: {s.name})")
        print("  [0] Enter custom folder path or Stack ID")
        choice = input("\nChoose a number or type Stack ID/path: ").strip()
        if choice.isdigit():
            val = int(choice)
            if 1 <= val <= len(subdirs):
                return subdirs[val - 1]
            # If user typed stack ID directly (e.g. 24)
            candidate = Path("bonds_images") / choice
            if candidate.is_dir():
                return candidate
            elif val == 0:
                custom = input("Enter folder path or Stack ID: ").strip()
                p = Path(custom)
                if p.is_dir():
                    return p
                p2 = Path("bonds_images") / custom
                if p2.is_dir():
                    return p2
                print("[ERROR] Directory does not exist.")
                sys.exit(1)
        else:
            # User typed path
            p = Path(choice)
            if p.is_dir():
                return p
            p2 = Path("bonds_images") / choice
            if p2.is_dir():
                return p2

    custom = input("Enter the folder path or Stack ID (e.g. 24): ").strip()
    p = Path(custom)
    if p.is_dir():
        return p
    p2 = Path("bonds_images") / custom
    if p2.is_dir():
        return p2
    print(f"[ERROR] Directory '{custom}' does not exist.")
    sys.exit(1)


def upload_single_file(client, file_path: Path, remote_folder: str):
    """Uploads a single file using ImageKit SDK and returns its public URL."""
    file_name = file_path.name

    # Check for SDK v5 interface (client.files.upload)
    if hasattr(client, "files") and hasattr(client.files, "upload"):
        with open(file_path, "rb") as f:
            resp = client.files.upload(
                file=f,
                file_name=file_name,
                folder=remote_folder
            )
            # Response object has .url
            if hasattr(resp, "url"):
                return resp.url
            if isinstance(resp, dict) and "url" in resp:
                return resp["url"]
            return str(resp)

    # Check for older SDK interface (client.upload_file)
    elif hasattr(client, "upload_file"):
        with open(file_path, "rb") as f:
            resp = client.upload_file(
                file=f,
                file_name=file_name,
                options={"folder": remote_folder}
            )
            if hasattr(resp, "response_metadata") and hasattr(resp.response_metadata, "raw"):
                return resp.response_metadata.raw.get("url")
            if hasattr(resp, "url"):
                return resp.url
            if isinstance(resp, dict) and "url" in resp:
                return resp["url"]
            return str(resp)
    else:
        raise RuntimeError("Unsupported ImageKit SDK version.")


def main():
    import re
    load_env_file()
    imagekit = get_imagekit_client()
    target_folder = select_folder()

    valid_exts = {".jpg", ".jpeg", ".png", ".webp", ".avif"}
    image_files = sorted(
        [p for p in target_folder.iterdir() if p.is_file() and p.suffix.lower() in valid_exts],
        key=lambda p: (int(p.stem) if p.stem.isdigit() else float('inf'), p.stem)
    )

    if not image_files:
        print(f"\n[!] No supported images found in '{target_folder}'.")
        return

    print(f"\nFound {len(image_files)} images in '{target_folder}'.")
    
    # Sanitize remote folder for ImageKit (must only contain a-z, A-Z, 0-9, _, -, /)
    folder_name = target_folder.name
    clean_name = re.sub(r'[^a-zA-Z0-9_\-]', '_', folder_name).strip('_')
    if not clean_name:
        nums = re.findall(r'\d+', folder_name)
        clean_name = nums[0] if nums else "unnamed_stack"

    remote_folder = f"/bonds/{clean_name}"
    print(f"Remote ImageKit Folder: {remote_folder}")
    confirm = input("Start upload? [Y/n]: ").strip().lower()
    if confirm in ('n', 'no'):
        print("Upload cancelled.")
        return

    # Dictionary: { "bond_serial": "image_url" }
    uploaded_links = {}
    failed_uploads = []

    print("\n--- Starting Upload to ImageKit ---")
    for idx, img_path in enumerate(image_files, 1):
        serial = img_path.stem  # e.g., '4551' from '4551.jpg'
        print(f"[{idx}/{len(image_files)}] Uploading {img_path.name} (Serial: {serial}) ...", end=" ", flush=True)

        try:
            url = upload_single_file(imagekit, img_path, remote_folder)
            uploaded_links[serial] = url
            print(f"SUCCESS\n    -> {url}")
        except Exception as e:
            print(f"FAILED\n    -> Error: {e}")
            failed_uploads.append(img_path.name)

    print("\n=======================================================")
    print(f" Upload Complete: {len(uploaded_links)} successful, {len(failed_uploads)} failed.")
    print("=======================================================")

    if not uploaded_links:
        print("[!] No images were successfully uploaded. Exiting.")
        return

    # =================================================================
    # Save links to temporary JSON and call PHP script
    # =================================================================
    json_path = Path("uploaded_bonds_links.json")
    with open(json_path, "w", encoding="utf-8") as f:
        json.dump(uploaded_links, f, ensure_ascii=False, indent=2)

    print(f"\nSaved uploaded links mapping to: {json_path}")
    print("Calling PHP script 'update_bond_links.php' to save links to database...")

    try:
        result = subprocess.run(
            ["php", "update_bond_links.php", str(json_path)],
            capture_output=True,
            text=True,
            encoding="utf-8",
            check=True
        )
        print("\n" + result.stdout)
    except subprocess.CalledProcessError as e:
        print("\n[ERROR] PHP script execution failed:")
        print(e.stderr or e.stdout)
    except FileNotFoundError:
        print("\n[ERROR] 'php' command not found in PATH.")
        print(f"You can manually run:\n  php update_bond_links.php {json_path}")


if __name__ == "__main__":
    main()
