PDF_AUX=pdf-aux

proposal.pdf: proposal.tex
	pdflatex -output-directory=$(PDF_AUX) $<
	bibtex $(PDF_AUX)/proposal.aux
	pdflatex -output-directory=$(PDF_AUX) $<
	pdflatex -output-directory=$(PDF_AUX) $<
	xdg-open $(PDF_AUX)/proposal.pdf
	ln -f $(PDF_AUX)/proposal.pdf

clean:
	rm -f $(PDF_AUX)/* proposal.pdf
