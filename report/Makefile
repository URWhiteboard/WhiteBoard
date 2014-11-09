PDF_AUX=pdf-aux

report.pdf: report.tex
	pdflatex -output-directory=$(PDF_AUX) $<
	bibtex $(PDF_AUX)/report.aux
	pdflatex -output-directory=$(PDF_AUX) $<
	pdflatex -output-directory=$(PDF_AUX) $<
	xdg-open $(PDF_AUX)/report.pdf
	ln -f $(PDF_AUX)/report.pdf

clean:
	rm -f $(PDF_AUX)/* report.pdf
