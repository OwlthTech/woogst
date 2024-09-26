import os
import re



# Function to create the directory structure
def create_directory_structure(path, ignore_dirs=None, ignore_files=None):
    if ignore_dirs is None:
        ignore_dirs = []
    if ignore_files is None:
        ignore_files = []

    directory_structure = {}

    for dirpath, dirnames, filenames in os.walk(path):
        # Get relative path
        rel_dir = os.path.relpath(dirpath, path)
        if rel_dir == '.':
            rel_dir = ''

        # Ignore hidden directories and any directories in the ignore list
        dirnames[:] = [d for d in dirnames if not d.startswith('.') and os.path.join(rel_dir, d) not in ignore_dirs]

        # Ignore hidden files and any files in the ignore list
        filenames = [f for f in filenames if not f.startswith('.') and f not in ignore_files]

        # Add files to the directory structure
        directory_structure[rel_dir] = filenames

    return directory_structure

# Function to write the directory structure to markdown
def write_directory_structure_to_md(directory_tree, output_file):
    with open(output_file, 'w') as f:
        for folder, files in directory_tree.items():
            if folder:
                f.write(f"### {folder}/\n")
            else:
                f.write(f"### Root/\n")
            for file in files:
                link = file.replace(' ', '%20')
                file_id = file.replace('.', '_').replace(' ', '_').lower()
                f.write(f"  - [{file}](#{file_id})\n")

# Function to clean and extract PHP content
def clean_php_content(content):
    content = re.sub(r'//.*', '', content)
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    return content

def extract_php_details(file_content):
    cleaned_content = clean_php_content(file_content)
    classes = re.findall(r'class\s+(\w+)', cleaned_content)
    functions = re.findall(r'function\s+(\w+)\s*\(([^)]*)\)', cleaned_content)
    
    details = {
        'classes': classes,
        'functions': [{'name': func[0], 'params': func[1]} for func in functions]
    }
    return details

# Function to write file details to markdown
def write_file_details_to_md(directory_tree, output_file, plugin_path):
    with open(output_file, 'a') as f:
        for folder, files in directory_tree.items():
            for file in files:
                if file.endswith('.php'):
                    file_path = os.path.join(plugin_path, folder, file)
                    with open(file_path, 'r', encoding='utf-8') as php_file:
                        content = php_file.read()
                        details = extract_php_details(content)
                        file_id = file.replace('.', '_').replace(' ', '_').lower()
                        f.write(f"\n\n## {file} <a id=\"{file_id}\"></a>\n")
                        if details['classes']:
                            f.write("### Classes\n")
                            for cls in details['classes']:
                                f.write(f"- {cls}\n")
                        if details['functions']:
                            f.write("### Functions\n")
                            for func in details['functions']:
                                f.write(f"- **{func['name']}**\n")
                                # Only write the parameters if they are valid and meaningful
                                if func['params'] and func['params'].strip() and func['params'].strip() != '':
                                    f.write(f"  - Parameters: {func['params']}\n")

# Generate the directory structure
plugin_path = os.getcwd()
ignore_dirs = [".git", "docs", "logs"]
ignore_files = ["readme.txt", "LICENSE.TXT", "directory_structure.py", "php_functions.py", "readme.md"]
directory_tree = create_directory_structure(plugin_path, ignore_dirs, ignore_files)

# Define the output markdown files in the docs directory
docs_directory = os.path.join(plugin_path, 'docs')
if not os.path.exists(docs_directory):
    os.makedirs(docs_directory)
output_md_structure_file = os.path.join(docs_directory, 'directory_structure.md')
output_md_details_file = os.path.join(docs_directory, 'file_details.md')

# Write the directory structure and file details to the docs directory
write_directory_structure_to_md(directory_tree, output_md_structure_file)
write_file_details_to_md(directory_tree, output_md_details_file, plugin_path)

print(f"Directory structure written to {output_md_structure_file}")
print(f"File details written to {output_md_details_file}")