document.addEventListener('DOMContentLoaded', () => {
    const treeItems = document.querySelectorAll('.tree-item');

    treeItems.forEach(item => {
        const content = item.querySelector('.tree-content');
        const subList = item.querySelector('ul');

        if (subList) {
            content.addEventListener('click', () => {
                // Toggle active class
                item.classList.toggle('active');

                // Add animation classes
                if (item.classList.contains('active')) {
                    subList.classList.add('animate__animated', 'animate__fadeIn');
                } else {
                    subList.classList.add('animate__animated', 'animate__fadeOut');
                }

                // Remove animation classes after animation ends
                subList.addEventListener('animationend', () => {
                    subList.classList.remove('animate__animated', 'animate__fadeIn', 'animate__fadeOut');
                });
            });
        }
    });

    // Add hover animation for all tree content items
    const treeContents = document.querySelectorAll('.tree-content');
    treeContents.forEach(content => {
        content.addEventListener('mouseenter', () => {
            const icon = content.querySelector('i');
            icon.classList.add('animate__animated', 'animate__rubberBand');
        });

        content.addEventListener('mouseleave', () => {
            const icon = content.querySelector('i');
            icon.classList.remove('animate__animated', 'animate__rubberBand');
        });
    });
});