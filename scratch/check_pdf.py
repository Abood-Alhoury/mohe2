import pypdf

def read_pdf(filename):
    print(f"=== {filename} ===")
    reader = pypdf.PdfReader(filename)
    for i, page in enumerate(reader.pages):
        print(f"--- Page {i+1} ---")
        print(page.extract_text())

try:
    read_pdf('scratch/pdf_test1_raw.pdf')
    read_pdf('scratch/pdf_test2_arphp_ltr.pdf')
except Exception as e:
    print(e)
