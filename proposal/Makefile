PDFlATEX_DIR=pdf-aux

proposal.pdf: proposal.tex
	pdflatex -output-directory=$(PDFlATEX_DIR) $< > $(PDFlATEX_DIR)/OUTPUT
	xdg-open $(PDFlATEX_DIR)/proposal.pdf
	ln -f $(PDFlATEX_DIR)/proposal.pdf

clean:
	rm -f $(PDFlATEX_DIR)/* proposal.pdf
