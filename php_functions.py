import os
import re

def clean_php_content(content):
    # Remove single-line comments
    content = re.sub(r'//.*', '', content)
    # Remove multi-line comments
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    return content

def extract_php_details(file_content):
    cleaned_content = clean_php_content(file_content)
    
    # Extract classes and functions with details
    classes = re.findall(r'class\s+(\w+)', cleaned_content)
    functions = re.findall(r'function\s+(\w+)\s*\(([^)]*)\)', cleaned_content)
    
    details = {
        'classes': classes,
        'functions': [{
            'name': func[0],
            'params': func[1]
        } for func in functions]
    }
    
    return details


def write_file_details_to_md(directory_tree, output_file, plugin_path):
    with open(output_file, 'a') as f:
        for folder, files in directory_tree.items():
            for file in files:
                if file.endswith('.php'):  # Process only PHP files
                    file_path = os.path.join(plugin_path, folder, file)
                    with open(file_path, 'r', encoding='utf-8') as php_file:
                        content = php_file.read()
                        details = extract_php_details(content)
                        
                        # Create file section with jump link
                        file_id = file.replace('.', '_').replace(' ', '_').lower()
                        f.write(f"\n\n## {file} <a id=\"{file_id}\"></a>\n")
                        
                        # Write classes
                        if details['classes']:
                            f.write("### Classes\n")
                            for cls in details['classes']:
                                f.write(f"- {cls}\n")
                        
                        # Write functions
                        if details['functions']:
                            f.write("### Functions\n")
                            for func in details['functions']:
                                f.write(f"- **{func['name']}**\n")
                                f.write(f"  - Parameters: {func['params']}\n")

# Write file details to markdown
details_md_file = 'file_details.md'
write_file_details_to_md(directory_tree, details_md_file, plugin_path)

print(f"File details written to {details_md_file}")