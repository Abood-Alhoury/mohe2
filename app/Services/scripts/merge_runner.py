import sys
import json
from pypdf import PdfWriter, PdfReader

def main():
    if len(sys.argv) < 3:
        print("Usage: python merge_runner.py <input_json> <output_pdf>", file=sys.stderr)
        sys.exit(1)

    input_json = sys.argv[1]
    output_pdf = sys.argv[2]

    try:
        with open(input_json, 'r', encoding='utf-8') as f:
            file_list = json.load(f)
    except Exception as e:
        print(f"ERROR_READ_JSON: {e}", file=sys.stderr)
        sys.exit(1)

    writer = PdfWriter()
    total_pages = 0

    for file_path in file_list:
        try:
            reader = PdfReader(file_path)
            for page in reader.pages:
                writer.add_page(page)
                total_pages += 1
        except Exception as e:
            # Skip corrupted or unreadable PDF files gracefully
            print(f"WARN_SKIP_FILE: {file_path}: {e}", file=sys.stderr)

    try:
        with open(output_pdf, 'wb') as f:
            writer.write(f)
        print(f"SUCCESS:{total_pages}")
    except Exception as e:
        print(f"ERROR_WRITE_PDF: {e}", file=sys.stderr)
        sys.exit(2)

if __name__ == '__main__':
    main()
