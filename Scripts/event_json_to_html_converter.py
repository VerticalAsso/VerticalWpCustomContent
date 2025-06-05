from pathlib import Path
import re
import html

def htmlify_body_content(json_html_body):
    # Replace JSON-style line breaks with real line breaks
    html_text = json_html_body.replace("\\r\\n", "\n").replace("\\n", "\n").replace("\\t", "    ")
    # Unescape HTML entities
    html_text = html.unescape(html_text)
    
    # Regex for HTML block elements (add more if needed)
    block_tags = [
        "ul", "ol", "li", "img", "a", "span", "div", "strong", "em", "table", "tr", "td", "th", "thead", "tbody", "tfoot", "h[1-6]", "blockquote"
    ]
    block_tag_re = re.compile(
        r"(<(?:%s)[^>]*>.*?</(?:%s)>)" % ("|".join(block_tags), "|".join(block_tags)),
        re.DOTALL | re.IGNORECASE
    )
    # Also match standalone block tags (e.g. <img .../>)
    single_tag_re = re.compile(r"(<(?:img|br|hr)[^>]*>)", re.IGNORECASE)

    # Split into blocks: either HTML or text
    # We'll temporarily mark block HTML with placeholders
    placeholder = "###HTML_BLOCK_%d###"
    html_blocks = []
    def repl_block(m):
        html_blocks.append(m.group(0))
        return placeholder % (len(html_blocks)-1)
    html_text = block_tag_re.sub(repl_block, html_text)
    html_text = single_tag_re.sub(repl_block, html_text)

    # Now, split remaining text by two or more newlines (paragraphs)
    def wrap_paragraphs(text):
        paras = [p.strip() for p in re.split(r"\n\s*\n", text)]
        out = []
        for p in paras:
            if not p:
                continue
            out.append(f"<p>{p}</p>")
        return "\n".join(out)

    html_text = wrap_paragraphs(html_text)

    # Restore HTML blocks
    def restore_block(m):
        idx = int(m.group(1))
        return html_blocks[idx]
    html_text = re.sub(r"###HTML_BLOCK_(\d+)###", restore_block, html_text)

    return f"""<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Generated Page</title>
</head>
<body>
{html_text}
</body>
</html>
"""

if __name__ == "__main__":
    script_dir = Path(__file__).parent
    cache_dir = script_dir.joinpath(".cache")
    data_dir = script_dir.joinpath("Data")
    db_record_file = data_dir.joinpath("db_record.txt")

    # Single line record
    print("Opening db record file")
    content : str = ""
    with open(db_record_file, "r", encoding='utf-8') as file :
        content = file.readline()

    html_result = htmlify_body_content(content)
    output_filepath = cache_dir.joinpath("output.html")
    with open(output_filepath, "w", encoding="utf-8") as f:
        f.write(html_result)
    print(f"{output_filepath} written.")